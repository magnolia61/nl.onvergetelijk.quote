# CiviCRM Extension: Quote Processor (nl.onvergetelijk.quote)

Deze extensie automatiseert het beheer van Quote-activiteiten op basis van custom velden in de tab **Promotie** (Groep ID 150). Wanneer een quote wordt ingevuld of een akkoorddatum wordt gezet, maakt of updatet deze module automatisch de bijbehorende activiteit voor de deelnemer, ouder of leiding.

## 🚀 Functionaliteit

De module reageert op wijzigingen in specifieke custom velden en synchroniseert deze met CiviCRM Activiteiten:

### Veldmapping & Activiteiten

| Type | Quote Veld | Akkoord Veld | Datum Veld | Activiteit Type ID |
| :--- | :--- | :--- | :--- | :--- |
| **Deelnemer** | custom_631 | custom_648 | custom_1091 | 126 (QUOTE_deel) |
| **Ouder** | custom_632 | custom_642 | custom_1092 | 127 (QUOTE_ouder) |
| **Leiding** | custom_633 | custom_644 | custom_1090 | 129 (QUOTE_leid) |

### Logica per Quote
* **CREATE**: Maakt een nieuwe activiteit aan als er een quote is ingevuld en er nog geen activiteit bestaat.
* **UPDATE**: Zet de status op **Completed** (2) zodra er een akkoorddatum aanwezig is, anders blijft deze op **Scheduled** (1).
* **DELETE**: Verwijdert de activiteit automatisch als de quote-tekst in het contactprofiel wordt leeggemaakt.
* **FILTER**: Verwerkt alleen wijzigingen binnen Custom Group 150 (Promotie).

---

## 🛠 Ontwikkeling & Debugging

De extensie maakt gebruik van een uitgebreide `wachthond` logging voor traceerbaarheid in de CiviCRM/Drupal logs.

### Log Voorbeeld

http://googleusercontent.com/immersive_entry_chip/0

**Zal ik nu ook nog even de schone "Productie 1.0" versie van de `FlexListener.php` (Loctype module) voor je genereren, zodat die ook direct mee kan in deze batch?**
