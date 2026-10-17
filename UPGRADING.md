# Upgrading to 2.0

Sequora now requires `spriebsch/domain-event` 3.0. Every event stored through Sequora must implement the new JSON contract (`asArray()`, `jsonSerialize()`, and `fromJson()`). See the domain-event 3.0 upgrade guide for details.

Sequora does not migrate previously stored event JSON. Recreate or migrate existing event stores before reading them with this version.
