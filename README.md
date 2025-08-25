# CiviCRM MOCO Integration

## Beschreibung

Diese Extension integriert MOCO (Projektmanagement- und Zeiterfassungssoftware) in CiviCRM. Sie ermöglicht es, Firmendaten, Projekte und Umsätze aus MOCO direkt in den CiviCRM-Kontakt-Ansichten anzuzeigen.

## Features

- **Firmen-Integration**: Anzeige von MOCO-Firmendaten in CiviCRM-Kontakten
- **Projekt-Übersicht**: Liste aller Projekte mit Status und Budget
- **Umsatz-Dashboard**: Zusammenfassung von Rechnungen und offenen Beträgen
- **Aktivitäten**: Zeiterfassungen und Projektaktivitäten
- **Caching**: Konfigurierbare Zwischenspeicherung für bessere Performance
- **Sichere API-Integration**: Verschlüsselte Übertragung aller Daten

## Installation

1. Laden Sie die Extension in das CiviCRM-Extensions-Verzeichnis:

   ```
   sites/default/files/civicrm/ext/civicrm_moco_integration/
   ```

2. Aktivieren Sie die Extension über die CiviCRM-Administration oder mit cv:

   ```bash
   cv en civicrm_moco_integration
   ```

3. Konfigurieren Sie die Extension unter:
   **Administer > System Settings > MOCO Integration Settings**

## Konfiguration

### Erforderliche Einstellungen:

1. **MOCO API Key**: Ihr persönlicher API-Schlüssel aus MOCO
2. **MOCO Domain**: Ihre MOCO-Subdomain (z.B. "ihr-unternehmen" für ihr-unternehmen.mocoapp.com)
3. **Custom Field**: Wählen Sie ein benutzerdefiniertes Feld, das die MOCO-ID enthält
4. **ID Type**: Legen Sie fest, ob Sie Company- oder Contact-IDs verwenden

### Optionale Einstellungen:

- **Caching aktivieren**: Verbessert die Performance (empfohlen)
- **Cache TTL**: Zeit in Sekunden für die Zwischenspeicherung (Standard: 300)

## Verwendung

Nach der Konfiguration erscheint automatisch ein neuer Tab "MOCO Data" in den Kontakt-Ansichten von Firmen, die eine MOCO-ID haben.

Der Tab zeigt folgende Informationen:

- **Umsatz-Zusammenfassung**: Gesamtumsatz, offene Rechnungen, aktive Projekte
- **Projekt-Liste**: Alle Projekte mit Status und Budget
- **Aktivitäten**: Letzte Zeiterfassungen und Projektaktivitäten

## Systemvoraussetzungen

- CiviCRM 5.50+
- PHP 7.4+
- cURL-Unterstützung
- Gültiger MOCO-Account mit API-Zugang



