# Obiettivi dei miglioramenti per amazon-alexa-php

Questo documento definisce gli obiettivi strategici dei miglioramenti pianificati per il framework PHP per Alexa Skills.

## Obiettivi principali

### 1. Kernel / SkillApplication
- **Obiettivo**: Ridurre il boilerplate di bootstrap e fornire un entrypoint unico per le skill
- **Problema risolto**: Ogni progetto deve riscrivere la stessa "colla" per routing, validazione, gestione errori
- **Risultato atteso**: Sviluppatori possono creare una skill con pochi comandi invece di dover orchestrare manualmente tutti i componenti

### 2. Intent routing migliorato  
- **Obiettivo**: Semplificare la registrazione e il routing degli intent
- **Problema risolto**: Pattern manuale con molte classi e logica `supportsRequest` ripetitiva
- **Risultato atteso**: API espressiva tipo `$app->intent('MyIntent')->handledBy(MyHandler::class)`

### 3. Response builder fluente
- **Obiettivo**: Rendere la creazione delle risposte più espressiva e meno verbosa
- **Problema risolto**: `ResponseHelper` richiede molti step separati e non ha una vera API fluente
- **Risultato atteso**: `ResponseBuilder::create()->text('...')->reprompt('...')->card($card)->endSession()`

### 4. Miglioramento RequestValidator
- **Obiettivo**: Rendere `RequestValidator` più configurabile e allineato agli standard moderni
- **Problema risolto**: Dipendenza diretta da Guzzle, configurazione limitata, gestione certificati monolitica
- **Risultato atteso**: Supporto PSR-18, configurabilità cache certificati, flag per disabilitare validazione in dev

### 5. Migliorie SsmlGenerator
- **Obiettivo**: Modernizzare l'API SSML per renderla più comoda
- **Problema risolto**: API puramente imperativa senza possibilità di chaining
- **Risultato atteso**: API fluente con metodi che restituiscono `$this` e factory statica

## Requisiti di retrocompatibilità

- **Compatibilità 2.x**: Tutte le nuove funzionalità devono essere introdotte in modo retrocompatibile
- **API additive**: Le nuove API (`SkillApplication`, `ResponseBuilder`, `IntentRouter`) sono additive
- **Breaking changes futuri**: Eventuali cambiamenti incompatibili dovranno essere deprecati prima di essere rimossi in una major release futura (es. 3.0.0)

## Metriche di successo

1. **Riduzione boilerplate**: Un esempio basic dovrebbe passare da ~50 linee a ~15 linee
2. **Developer Experience**: API più intuitive e meno error-prone
3. **Testability**: Componenti più modulari e facilmente testabili
4. **Adozione standard**: Allineamento a PSR-18 e altri standard PHP moderni
