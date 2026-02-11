## Piano di lavoro tecnico per i miglioramenti

Questo piano è organizzato in fasi e priorità, in modo da poter introdurre i cambiamenti in modo incrementale e mantenere la compatibilità esistente.

---

### Fase 0 – Preparazione e groundwork

1. **Allineamento obiettivi**
   - Documentare in un issue o in `CONTRIBUTING.md` gli obiettivi dei miglioramenti:
     - Kernel / `SkillApplication`
     - Intent routing migliorato
     - Response builder fluente
     - Miglioramento `RequestValidator`
     - Migliorie a `SsmlGenerator`
   - Definire i requisiti di retrocompatibilità (evitare breaking change nella 2.x se possibile).

2. **Struttura documentazione**
   - Utilizzare la cartella `docs/` per:
     - `IMPROVEMENTS.md` (questo file riepilogo)
     - `WORKPLAN.md` (questo piano di lavoro)
     - Futuri documenti: `KERNEL.md`, `RESPONSE_BUILDER.md`, `SSML.md`, etc.

---

### Fase 1 – SkillApplication / AlexaKernel (Priorità ALTA)

**Obiettivo**: ridurre il boilerplate di bootstrap e fornire un entrypoint unico per le skill.

1. **Design API**
   - Nuova classe in `src/` (es. `src/Application/SkillApplication.php`):
     - Dipendenze principali via costruttore:
       - `RequestValidator`
       - `RequestHandlerRegistry` (o nuovo router)
     - Metodi pubblici proposti:
       - `fromGlobals(): self` (helper per leggere `php://input` e headers `$_SERVER`).
       - `handleRaw(string $requestBody, array $headers): Response`.
       - `handlePsrRequest(ServerRequestInterface $request): Response` (opzionale PSR-7).

2. **Implementazione iniziale**
   - Implementare:
     - Parsing richiesta: `Request::fromAmazonRequest($body, $certUrl, $signature)`.
     - Validazione: `RequestValidator::validate($request)`.
     - Selezione handler: `RequestHandlerRegistry::getSupportingHandler($request)`.
     - Invocazione handler e ritorno `Response`.
   - Gestione errori:
     - Catch di eccezioni di validazione → mappa a errori HTTP o ad un `ErrorResponseFactory` minimo.

3. **Esempio aggiornato**
   - Aggiornare `examples/simple-intent-request.php` o aggiungere un nuovo esempio:
     - `examples/skill-application-basic.php` che utilizza `SkillApplication`.

4. **Test**
   - Aggiungere test unitari per `SkillApplication`:
     - Richiesta valida → passa al giusto handler.
     - Richiesta con firma non valida → genera errore/risposta adeguata.
   - Assicurarsi che i test esistenti continuino a passare.

---

### Fase 2 – IntentRouter / IntentHandlerRegistry (Priorità ALTA)

**Obiettivo**: semplificare la registrazione e il routing degli intent.

1. **Design IntentRouter**
   - Nuova classe, es. `src/RequestHandler/IntentRouter.php`:
     - API di base:
       - `onIntent(string $intentName, AbstractRequestHandler $handler): self`.
       - `onLaunch(AbstractRequestHandler $handler): self`.
       - `onSessionEnded(AbstractRequestHandler $handler): self`.
       - `onFallback(AbstractRequestHandler $handler): self`.
     - Metodo:
       - `getHandlerFor(Request $request): AbstractRequestHandler`.

2. **Integrazione con SkillApplication**
   - `SkillApplication` può accettare un `IntentRouter` invece di un semplice `RequestHandlerRegistry`, oppure:
     - Tenere `RequestHandlerRegistry` per retrocompatibilità.
     - Aggiungere un path di utilizzo alternativo tramite router.

3. **Implementazione routing**
   - Per `IntentRequest`:
     - Leggere `intent->name` e risolvere l’handler registrato.
   - Per altri tipi di richiesta:
     - `LaunchRequest` → handler di launch.
     - `SessionEndedRequest` → handler di session end.
     - Fallback generico se nessun handler applicabile.

4. **Esempi e test**
   - Nuovi esempi in `examples/`:
     - `examples/intent-router-basic.php`.
   - Test:
     - Intent conosciuto → handler corretto.
     - Intent sconosciuto → fallback (se definito) o eccezione.

---

### Fase 3 – Response Builder fluente (Priorità ALTA/MEDIA)

**Obiettivo**: rendere la creazione delle risposte più espressiva e meno verbosa.

1. **Design ResponseBuilder**
   - Nuova classe, es. `src/Response/ResponseBuilder.php`:
     - Metodi fluenti:
       - `static create(): self`.
       - `text(string $text): self`.
       - `ssml(string $ssml): self`.
       - `reprompt(string $text): self`.
       - `repromptSsml(string $ssml): self`.
       - `card(Card $card): self`.
       - `directive(Directive $directive): self`.
       - `endSession(bool $end = true): self`.
       - `keepSession(): self`.
       - `build(): Response`.

2. **Integrazione con ResponseHelper**
   - Implementare `ResponseHelper` internamente usando `ResponseBuilder`:
     - `respond()` → `ResponseBuilder::create()->text($text)->endSession($endSession)->build()`.
     - `respondSsml()`, `reprompt()`, ecc. analoghi.
   - Non cambiare la firma pubblica di `ResponseHelper` (per compatibilità).

3. **Aggiornamento README / esempi**
   - Aggiungere una sezione “Response Builder” con esempi:
     - Risposte semplici.
     - Risposte con APL + card + reprompt.

4. **Test**
   - Nuovi test per `ResponseBuilder`.
   - Aggiornare/aggiungere test per `ResponseHelper` che verifichino l’integrazione.

---

### Fase 4 – Miglioramenti a RequestValidator (Priorità MEDIA)

**Obiettivo**: rendere `RequestValidator` più configurabile e allineato agli standard moderni.

1. **Adozione PSR-18 (opzionale ma consigliato)**
   - Introdurre dipendenze opzionali via Composer (`psr/http-client`, `psr/http-factory`).
   - Modificare il costruttore di `RequestValidator` per accettare:
     - `ClientInterface $client` (PSR-18) invece di `GuzzleHttp\Client` concreti.
     - Mantenere supporto a `GuzzleHttp\Client` tramite adapter se necessario.

2. **Configurabilità**
   - Estendere il costruttore con:
     - `?string $certCacheDir = null` (default: `sys_get_temp_dir()`).
     - `bool $disableSignatureValidation = false` (da usare solo in dev/test).
   - Aggiornare `fetchCertData()` per usare `certCacheDir`.
   - Nella logica di validazione:
     - Se `disableSignatureValidation === true`, saltare la verifica della firma (con chiaro commento/warning).

3. **Refactor CertValidator**
   - Estrarre la logica certificati in una classe dedicata, es. `src/Validation/CertValidator.php`:
     - Responsabilità: fetch/parse/validate cert, cache, ecc.
     - `RequestValidator` delega queste responsabilità.

4. **Test**
   - Aggiornare `RequestValidatorTest` per coprire:
     - Cache custom.
     - Flag di disabilitazione.
     - Utilizzo di un client mock PSR-18.

---

### Fase 5 – SsmlGenerator e API SSML (Priorità MEDIA)

**Obiettivo**: rendere `SsmlGenerator` più moderno e comodo.

1. **API fluente**
   - Estendere i metodi di `SsmlGenerator` per restituire `$this` (in aggiunta al comportamento attuale) dove possibile, ad es.:
     - `public function say(string $text): self`.
     - `public function pauseTime(string $time): self`.
   - Mantenere compatibilità retro:
     - I metodi possono continuare a funzionare anche se ignorato il valore di ritorno.

2. **Factory statica**
   - Aggiungere `SsmlGenerator::create(bool $escapeSpecialChars = false): self`.
   - Permettere un uso tipo:
     - `$ssml = SsmlGenerator::create()->say('Ciao')->pauseTime('2s')->getSsml();`.

3. **Helper per pattern comuni**
   - Valutare metodi helper:
     - `number(int $n): self` per leggere un numero (es. utile in giochi tipo tombola).
     - `list(array $items): self` per elencare elementi con pause configurabili.

4. **Test**
   - Estendere `SsmlGeneratorTest` per coprire uso fluente.

---

### Fase 6 – Miglioramenti minori e manutenzione (Priorità BASSA)

1. **Naming**
   - Introdurre alias/nuove classi per nomi meno fortunati (es. `OutdatedCertExceptionException` → `OutdatedCertException`), mantenendo:
     - La vecchia classe come estensione della nuova (deprecata).

2. **Composer / minimum-stability**
   - Valutare, in base allo stato del codice e dei test:
     - Rimuovere `"minimum-stability": "dev"` o impostare `"prefer-stable": true`.
   - Aggiornare `CHANGELOG.md` e README con note su stabilità.

3. **Linee guida per struttura progetto utente**
   - Aggiungere in `docs/` un documento `PROJECT_STRUCTURE.md` che suggerisca:
     - Dove mettere gli handler.
     - Come organizzare dominio / intent / servizi.

---

### Fase 7 – Nuove funzionalità opzionali

1. **Session Manager**
   - Nuova classe `Helper/SessionHelper.php` con metodi statici:
     - `getAttribute(Request $request, string $key, mixed $default = null): mixed`.
     - `setAttribute(Response $response, string $key, mixed $value): void`.
   - Integrare esempi nel README.

2. **Intent Testing Utilities**
   - Nuova factory in `test/` (e/o `src/TestSupport/`) per creare facilmente richieste:
     - `IntentRequestFactory::forIntent(string $name, array $slots = []): Request`.
   - Usare la factory nei test esistenti per ridurre boilerplate.

3. **CLI generator (facoltativo)**
   - Script in `bin/` o `tools/`:
     - `php bin/alexa make:intent-handler MyIntentHandler --intent=MyIntent`.
   - Genera file skeleton di handler nella cartella indicata dall’utente.

---

### Note su compatibilità e versioning

- **Compatibilità 2.x**
  - Tutte le nuove funzionalità dovrebbero essere introdotte in modo retrocompatibile.
  - Le API nuove (`SkillApplication`, `ResponseBuilder`, `IntentRouter`) sono additive.

- **Breaking changes futuri**
  - Eventuali cambiamenti incompatibili (es. rimozione di campi public, rinomina di eccezioni) dovrebbero essere:
    - Prioriamente deprecati in una minor release.
    - Rimossi solo in una futura major (es. 3.0.0), con guida di migrazione.

