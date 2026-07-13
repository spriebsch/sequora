# Sequora

The Next-Generation Event Store.

Sequora is a made-up word combining the components sequence and aura. Sequence because an event store must store events in a strict sequence. Aura because it is a source of insight and knowledge and represents the fact that an event store is the authoritative source of truth in an event-based system.

Sequora was created by Stefan Priebsch <stefan@priebsch.de>.

## Installation

Install Sequora via Composer:

```bash
composer require spriebsch/sequora
```

## Usage

### Initializing the Event Store

Sequora uses SQLite as its storage engine. You need to provide a `SqliteConnection` and initialize the schema.

```php
use spriebsch\sequora\SqliteSequoraSchema;
use spriebsch\sqlite\SqliteConnection;

$connection = SqliteConnection::fromMemory();
SqliteSequoraSchema::from($connection)->init();
```

### Writing Events

To write events, use the `SequoraWriter`. It accepts `DomainEvent` instances.

```php
use spriebsch\sequora\SequoraWriter;
use spriebsch\sequora\SqliteDatabaseWriter;

$dbWriter = SqliteDatabaseWriter::from($connection);
$writer = SequoraWriter::from($dbWriter);

$writer->store($event1, $event2);
```

### Reading Events

To read events, use the `SequoraReader`. You need to provide a topic map that maps event topics to their corresponding PHP classes.

```php
use spriebsch\sequora\SequoraReader;
use spriebsch\sequora\SqliteDatabaseReader;

$topicMap = [
    'vendor.domain.context.event_name' => MyEvent::class,
];

$dbReader = SqliteDatabaseReader::from($connection, $topicMap);
$reader = SequoraReader::from($dbReader);

// Read all events
$events = $reader->all();

foreach ($events as $envelope) {
    $event = $envelope->payload()->asEvent();
    // ...
}
```

### Generating a Topic Map

You can generate a topic map by running the `generate-topic-map` tool. It scans a directory for classes that implement `DomainEvent` and have a `Topic` attribute. The generated file `TopicMap.php` will be placed in the specified directory.

```bash
vendor/bin/generate-topic-map <directory-with-events>
```

The generated file returns an associative array that you can use when initializing the `SqliteDatabaseReader`:

```php
$topicMap = require __DIR__ . '/path/to/TopicMap.php';
$dbReader = SqliteDatabaseReader::from($connection, $topicMap);
```

### Querying Events

You can filter events using `EventQuery`.

```php
use spriebsch\sequora\EventQuery;
use spriebsch\DomainEvent\Topic;

$query = EventQuery::from()
    ->withTopics(Topic::fromString('vendor.domain.context.event_name'))
    ->limit(10);

$events = $reader->query($query);
```
