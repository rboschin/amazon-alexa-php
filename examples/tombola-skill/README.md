# Tombola Napoletana - Alexa Skill

Skill Alexa per giocare alla tombola con lettura automatica dei numeri e integrazione della smorfia napoletana, sviluppata con il framework Alexa PHP.

## Caratteristiche

- ✅ **Estrazione numeri** da 1 a 90 con controllo duplicati
- ✅ **Lettura numero** in tre parti: numero intero, cifre separate, smorfia
- ✅ **Persistenza sessioni** su database SQLite
- ✅ **Comandi vocali** completi in italiano
- ✅ **Modalità multiple**: normale, lenta, automatica
- ✅ **Verifica vincite**: ambo/terna/quaterna/cinquina/tombola
- ✅ **SSML avanzato** per lettura naturale
- ✅ **Handler ottimizzati** con @utterances
- ✅ **Framework moderno** con type safety

## Requisiti

- PHP 8.0 o superiore
- Estensione PDO SQLite
- Composer

## Installazione

### 1. Clona il progetto

```bash
cd /home/roberto/packages/amazon-alexa-php/examples/tombola-skill
```

### 2. Installa le dipendenze

```bash
composer install
```

### 3. Configura l'ambiente

Copia `.env.example` in `.env` e configura:

```bash
cp config/.env.example config/.env
```

Modifica `config/.env`:
```
DB_PATH=./data/tombola.db
LOG_PATH=./logs/tombola.log
DEBUG=true
ALEXA_APP_ID=amzn1.ask.skill.YOUR_SKILL_ID_HERE
```

### 4. Crea le directory necessarie

```bash
mkdir -p data logs
chmod 755 data logs
```

## Utilizzo

### Sviluppo Locale

```bash
# Avvia il server di sviluppo
composer start

# Oppure manualmente
php -S localhost:8000 -t public
```

### Testing

```bash
# Esegui i test
composer test

# Analisi statica
composer analyze
```

### Generazione Interaction Model

```bash
# Genera automaticamente l'interaction model dagli handler
composer generate-model
```

## Comandi Vocali

### Apertura skill
- `"Alexa apri tombola napoletana"` 

### Gioco Base
- `"estrai un numero"` - Estrae un nuovo numero
- `"prossimo numero"` - Continua l'estrazione
- `"ripeti"` - Ripete l'ultimo numero
- `"è uscito il 25"` - Verifica se un numero è uscito

### Modalità
- `"modalità automatica"` - Estrae 5 numeri in sequenza
- `"modalità lenta"` - Lettura con ripetizioni
- `"modalità normale"` - Lettura standard

### Vincite
- `"ambo"` - Verifica ambo (2 numeri)
- `"terna"` - Verifica terna (3 numeri)
- `"quaterna"` - Verifica quaterna (4 numeri)
- `"cinquina"` - Verifica cinquina (5 numeri)
- `"tombola"` - Verifica tombola (15 numeri)

### Controllo Partita
- `"stato"` - Mostra statistiche della partita
- `"pausa"` - Mette in pausa la partita
- `"nuova partita"` - Inizia una nuova partita
- `"termina partita"` - Termina la partita corrente

### Aiuto
- `"aiuto"` - Mostra i comandi disponibili
- `"si/no"` - Risposte a domande di conferma

## Architettura

### Struttura Directory

```
src/
├── Handlers/           # Handler degli intent Alexa
├── Services/          # Logica di business
├── Models/            # Modelli dati
└── Config/           # Configurazione
```

### Componenti Principali

- **TombolaManager**: Logica core del gioco
- **SpeechBuilder**: Generazione SSML avanzata
- **DatabaseService**: Gestione database SQLite
- **SmorfiaService**: Gestione smorfia napoletana
- **RouterConfig**: Configurazione handler unificati

### Handler Ottimizzati

L'applicazione usa handler unificati per ridurre la manutenzione:

- `CheckWinningHandler` - Gestisce tutte le vincite
- `ModeHandler` - Gestisce tutte le modalità
- `GameControlHandler` - Gestisce pause/end/new game
- `YesNoHandler` - Gestisce Yes/No/Cancel/Stop

## Framework Features

### @utterances

Tutti gli handler includono annotazioni `@utterances` per generazione automatica:

```php
/**
 * Handler per l'estrazione di un nuovo numero
 * @utterances estrai un numero, prossimo numero, numero, avanti, vai
 * @utterances dammi un numero, nuovo numero, estrai
 */
class ExtractNumberHandler extends AbstractRequestHandler
```

### SSML Avanzato

Lettura numeri con prosodia naturale:

```php
$ssml = SsmlGenerator::create()
    ->sentence("Il numero estratto è:")
    ->break("500ms")
    ->emphasis((string)$number, "strong")
    ->break("1s")
    ->prosody($smorfia, "slow", "+10%")
    ->build();
```

### Session Management

Gestione typed degli attributi di sessione:

```php
return ResponseBuilder::create()
    ->withAttributes([
        'last_number' => $number,
        'reading_mode' => $mode,
        'extracted_count' => $count
    ])
    ->build();
```

## Database

### Schema

- **sessions**: Sessioni utente con numeri estratti
- **game_numbers**: Dettagli numeri estratti
- **user_numbers**: Numeri forniti per verifica

### Migration Automatica

Il database viene creato automaticamente al primo avvio con tutte le tabelle necessarie.

## Deployment

### 1. Prepara l'ambiente

```bash
composer install --no-dev --optimize-autoloader
```

### 2. Configura Alexa Console

1. Crea una nuova skill nella Alexa Developer Console
2. Carica l'interaction model generato con `composer generate-model`
3. Configura l'endpoint HTTPS pointing al tuo server
4. Imposta l'Application ID nel file `.env`

### 3. Testa la Skill

Usa il Alexa Simulator o un dispositivo Echo per testare tutti i comandi.

## Development

### Aggiungere Nuovi Intent

1. Crea un nuovo handler in `src/Handlers/`
2. Aggiungi annotazioni `@utterances`
3. Registra l'handler in `RouterConfig::createRouter()`
4. Rigenera l'interaction model

### Debug

Abilita il debug mode in `.env`:
```
DEBUG=true
```

## Licenza

MIT License - vedi file LICENSE per dettagli.

## Supporto

Per problemi o suggerimenti, controlla la documentazione del framework Alexa PHP.
