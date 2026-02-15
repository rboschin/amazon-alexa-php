# Piano di Lavoro Tecnico - Tombola Napoletana Skill con Framework Alexa PHP

## Obiettivo
Ricreare l'applicazione Tombola Napoletana utilizzando il framework Alexa PHP con le best practices e le funzionalità avanzate del framework.

## Analisi dell'Applicazione Esistente

### Funzionalità Principali
1. **Estrazione numeri** da 1 a 90 con controllo duplicati
2. **Lettura numero** in tre parti: numero intero, cifre separate, smorfia
3. **Persistenza sessioni** su database SQLite
4. **Comandi vocali**: estrai, ripeti, verifica, continua, ambo/terna/quaterna/cinquina/tombola
5. **Supporto lingua italiana** completa
6. **Gestione contesti** e stati di sessione

### Intent Implementati (Verificati)
- `LaunchRequest` - ✅ handleLaunchRequest()
- `ExtractNumberIntent` - ✅ handleExtractNumber()
- `ContinueIntent` - ✅ (gestito in handleExtractNumber)
- `RepeatNumberIntent` - ✅ handleRepeatNumber()
- `CheckNumberIntent` - ✅ handleCheckNumber()
- `VerifyAmboIntent` - ✅ handleGameVerificationRequest('ambo')
- `VerifyTernaIntent` - ✅ handleGameVerificationRequest('terna')
- `VerifyQuaternaIntent` - ✅ handleGameVerificationRequest('quaterna')
- `VerifyCinquinaIntent` - ✅ handleGameVerificationRequest('cinquina')
- `VerifyTombolaIntent` - ✅ handleGameVerificationRequest('tombola')
- `ProvideNumbersIntent` - ✅ handleProvideNumbers()
- `AutoModeIntent` - ✅ AutoModeHandler::handleAutoMode()
- `NormalModeIntent` - ✅ handleNormalMode()
- `SlowModeIntent` - ✅ (gestito in handleNormalMode)
- `StatusIntent` - ✅ handleStatus()
- `PauseIntent` - ✅ handlePause()
- `EndGameIntent` - ✅ handleEndGame()
- `NewGameIntent` - ✅ handleNewGame()
- `HelpIntent` - ✅ handleHelp()
- `CancelIntent` - ✅ handleStop()
- `StopIntent` - ✅ handleStop()
- `YesIntent` - ✅ handleYes()
- `NoIntent` - ✅ handleNo()

### Intent NON Implementati (Da Rimuovere)
- `ContinuousModeIntent` - ❌ Non implementato
- `AMAZON.PauseIntent` - ❌ Non implementato
- `AMAZON.FallbackIntent` - ❌ Non implementato
- `AMAZON.ResumeIntent` - ❌ Non implementato

### Ottimizzazione Struttura Handler
Invece di handler separati per ogni vincita, userò un handler parametrico:
- `CheckWinningHandler` - Gestisce ambo/terna/quaterna/cinquina/tombola
- `ModeHandler` - Gestisce auto/normal/slow modes
- `GameControlHandler` - Gestisce pause/end/new game

## Architettura con Framework Alexa PHP

### 1. Struttura Directory
```
tombola-skill/
├── src/
│   ├── Handlers/
│   │   ├── LaunchHandler.php
│   │   ├── ExtractNumberHandler.php
│   │   ├── RepeatNumberHandler.php
│   │   ├── CheckNumberHandler.php
│   │   ├── CheckWinningHandler.php        # Unificato per ambo/terna/quaterna/cinquina/tombola
│   │   ├── ModeHandler.php                # Unificato per auto/normal/slow modes
│   │   ├── GameControlHandler.php          # Unificato per pause/end/new game
│   │   ├── StatusHandler.php
│   │   ├── ProvideNumbersHandler.php
│   │   ├── HelpHandler.php
│   │   └── YesNoHandler.php               # Unificato per YesIntent/NoIntent
│   ├── Services/
│   │   ├── TombolaManager.php
│   │   ├── SpeechBuilder.php
│   │   ├── SmorfiaService.php
│   │   ├── DatabaseService.php
│   │   └── AutoModeService.php            # Estrae logica da AutoModeHandler
│   ├── Models/
│   │   ├── TombolaSession.php
│   │   └── WinningCombination.php
│   └── Config/
│       └── TombolaConfig.php
├── templates/
│   └── tombola-interaction-model.json
├── data/
│   └── tombola.db
├── logs/
├── config/
│   └── .env
├── public/
│   └── index.php
├── composer.json
└── README.md
```

### 2. Componenti Framework da Utilizzare

#### SkillApplication (Bootstrap)
- Utilizzo di `SkillApplication::fromGlobals()`
- Configurazione handler registry
- Integrazione con IntentRouter

#### IntentRouter (Routing Moderno)
- Registrazione intent con fluent API
- Gestione launch, help, cancel/stop integrati
- Fallback handler per errori

#### ResponseBuilder (Risposte Fluent)
- Costruzione risposte con SSML
- Gestione session attributes
- Supporto card e directives

#### SsmlGenerator (SSML Avanzato)
- Lettura numeri con prosodia
- Pause e enfasi per smorfia
- Audio tags se necessario

#### SessionHelper (Gestione Sessione)
- Accesso typed agli attributi
- Helper per contesto tombola

#### CLI Tools (Sviluppo)
- Generazione handler con utterances
- Generazione interaction model
- Template personalizzati

### 3. Database e Persistenza

#### DatabaseService (Singleton)
- Connessione SQLite
- Migration automatiche
- Query prepared statements

#### TombolaSession (Entity)
- Mappatura tabella sessions
- Relazioni con numeri estratti
- Gestione reading_mode

### 4. Business Logic

#### TombolaManager (Core)
- Estrazione numeri casuali
- Verifica vincite
- Gestione stato gioco
- Interazione con SmorfiaService

#### SpeechBuilder (SSML)
- Costruzione discorsi tombola
- Lettura multi-parte numeri
- Integrazione smorfia

#### SmorfiaService
- Caricamento smorfia da file
- Formattazione testo smorfia
- Cache ottimizzazione

## Piano di Sviluppo

### Fase 1: Setup e Struttura (Day 1)
1. **Creazione directory progetto**
2. **Setup composer.json** con dipendenze framework
3. **Configurazione ambiente** (.env, database)
4. **Creazione classi base** vuote

### Fase 2: Database e Servizi (Day 2)
1. **DatabaseService** - Connessione SQLite
2. **TombolaSession** - Model sessione
3. **SmorfiaService** - Caricamento smorfia
4. **Migration database** - Schema tabelle

### Fase 3: Business Logic (Day 3)
1. **TombolaManager** - Logica core
2. **SpeechBuilder** - Generazione SSML
3. **TombolaConfig** - Costanti e config
4. **Test unitari** per servizi

### Fase 4: Handler Principali (Day 4)
1. **LaunchHandler** - Benvenuto e ripresa sessione
2. **ExtractNumberHandler** - Estrazione numeri + gestione ContinueIntent
3. **RepeatNumberHandler** - Ripeti ultimo numero
4. **CheckNumberHandler** - Verifica numero uscito

### Fase 5: Handler Ottimizzati (Day 5)
1. **CheckWinningHandler** - Unificato per ambo/terna/quaterna/cinquina/tombola
2. **ModeHandler** - Unificato per auto/normal/slow modes
3. **GameControlHandler** - Unificato per pause/end/new game
4. **StatusHandler** - Stato partita
5. **ProvideNumbersHandler** - Fornitura numeri utente

### Fase 6: Handler Supporto (Day 6)
1. **HelpHandler** - Aiuto contestuale
2. **YesNoHandler** - Unificato per YesIntent/NoIntent
3. **FallbackHandler** - Gestione intent non riconosciuti

### Fase 7: Integrazione Framework (Day 7)
1. **SkillApplication** - Bootstrap
2. **IntentRouter** - Routing configurazione ottimizzato
3. **public/index.php** - Entry point
4. **Testing integrazione**

### Fase 8: Interaction Model (Day 8)
1. **Handler con @utterances** - Docblock completi
2. **CLI generation** - Modello automatico senza intent non implementati
3. **Customizzazione** - Slot types e synonyms
4. **Validazione** - Test con Alexa Console

### Fase 9: Ottimizzazioni (Day 9)
1. **Performance** - Cache e ottimizzazioni
2. **Error handling** - Gestione eccezioni
3. **Logging** - Debug e monitoring
4. **Documentation** - README e API docs

## Dettagli Tecnici

### @utterances Implementation
```php
/**
 * Handler per l'estrazione di un nuovo numero
 * @utterances estrai un numero, prossimo numero, numero, avanti, vai
 * @utterances dammi un numero, nuovo numero, estrai
 */
class ExtractNumberHandler extends AbstractRequestHandler
```

### IntentRouter Configuration (Ottimizzato)
```php
$router = IntentRouter::create()
    ->onLaunch(LaunchHandler::class)
    ->onIntent('ExtractNumberIntent', ExtractNumberHandler::class)
    ->onIntent('ContinueIntent', ExtractNumberHandler::class)  // Gestito insieme
    ->onIntent('RepeatNumberIntent', RepeatNumberHandler::class)
    ->onIntent('CheckNumberIntent', CheckNumberHandler::class)
    // Handler unificati per vincite
    ->onIntent('VerifyAmboIntent', CheckWinningHandler::class)
    ->onIntent('VerifyTernaIntent', CheckWinningHandler::class)
    ->onIntent('VerifyQuaternaIntent', CheckWinningHandler::class)
    ->onIntent('VerifyCinquinaIntent', CheckWinningHandler::class)
    ->onIntent('VerifyTombolaIntent', CheckWinningHandler::class)
    // Handler unificati per mode
    ->onIntent('AutoModeIntent', ModeHandler::class)
    ->onIntent('NormalModeIntent', ModeHandler::class)
    ->onIntent('SlowModeIntent', ModeHandler::class)
    // Handler unificati per game control
    ->onIntent('PauseIntent', GameControlHandler::class)
    ->onIntent('EndGameIntent', GameControlHandler::class)
    ->onIntent('NewGameIntent', GameControlHandler::class)
    // Altri handler
    ->onIntent('StatusIntent', StatusHandler::class)
    ->onIntent('ProvideNumbersIntent', ProvideNumbersHandler::class)
    ->onIntent('AMAZON.YesIntent', YesNoHandler::class)
    ->onIntent('AMAZON.NoIntent', YesNoHandler::class)
    ->onHelp(HelpHandler::class)
    ->onCancel(YesNoHandler::class)  // Gestito come No
    ->onStop(YesNoHandler::class)    // Gestito come No
    ->onFallback(FallbackHandler::class);
```

### SSML Advanced Features
```php
$ssml = SsmlGenerator::create()
    ->sentence("Il numero estratto è")
    ->break("500ms")
    ->emphasis((string)$number, "strong")
    ->break("1s")
    ->sentence("Cifre separate:")
    ->break("300ms")
    ->sayAs(implode(", ", str_split((string)$number)), "characters")
    ->break("1s")
    ->sentence("Nella smorfia napoletana:")
    ->break("300ms")
    ->prosody($smorfiaText, "slow", "+10%")
    ->build();
```

### Session Management
```php
// Get session attributes
$extractedCount = SessionHelper::getAttribute($request, 'extracted_count', 0);
$lastNumber = SessionHelper::getAttribute($request, 'last_number');
$readingMode = SessionHelper::getAttribute($request, 'reading_mode', 'full');

// Set session attributes
return ResponseBuilder::create()
    ->text($speech)
    ->withAttributes([
        'last_number' => $number,
        'last_smorfia' => $smorfiaText,
        'extracted_count' => $extractedCount + 1,
        'reading_mode' => $readingMode
    ])
    ->build();
```

## Vantaggi Framework vs Implementazione Originale

### ✅ Miglioramenti Apportati
1. **Type Safety** - Strict types e PHP 8+ features
2. **Dependency Injection** - Testabilità e modularità
3. **Fluent APIs** - Codice più leggibile e manutenibile
4. **SSML Avanzato** - SsmlGenerator per prosodia e audio
5. **Error Handling** - Gestione eccezioni strutturata
6. **CLI Tools** - Generazione automatica handler e model
7. **Session Helper** - Accesso typed agli attributi
8. **Intent Router** - Routing moderno e configurabile

### 🔄 Compatibilità Mantenuta
1. **Stessa logica di business** - TombolaManager preservato
2. **Stessi intent** - Mapping 1:1 con originali
3. **Stessa database** - Schema SQLite identico
4. **Stessa smorfia** - File smorfia.php riutilizzato
5. **Stesse utterances** - Comandi vocali identici

### 🚀 Nuove Funzionalità
1. **Interaction Model Auto-Generation** - CLI tools
2. **Advanced SSML** - Prosodia e pause dinamiche
3. **Better Error Messages** - Contextuali e utili
4. **Improved Testing** - Unit e integration tests
5. **Better Documentation** - Code examples e README
6. **Performance Monitoring** - Logging strutturato

## Deliverables Finali

1. **Applicazione completa** funzionante
2. **Interaction model** generato automaticamente
3. **Documentazione** tecnica e utente
4. **Test suite** unitari e integrazione
5. **Setup script** per deploy rapido
6. **CLI commands** per manutenzione

## Timeline Stimata
- **9 giorni** sviluppo completo
- **2 giorni** testing e debug
- **1 giorno** documentazione finale
- **Totale: 12 giorni** (~2.5 settimane)

### Risparmi Ottenuti dall'Ottimizzazione
- **4 handler in meno** da implementare separatamente
- **Routing semplificato** con handler unificati
- **Interaction model più pulito** senza intent non implementati
- **Manutenzione ridotta** grazie alla consolidazione

Questo piano garantisce una ricostruzione completa dell'applicazione con tutti i vantaggi del framework Alexa PHP mantenendo la compatibilità funzionale con l'originale.
