# JRC Cybersecurity Taxonomy

Version 1.0 - M.Marcello Verona <marcello.verona@lisboncouncil.net>

*This module has been developed in the context of the [COcyber Project](https://cocyber.eu), a project funded  by the Euopean Union - Project ID 101158606 - DIGITAL-ECCC-2023-DEPLOY-CYBER-04.*

Drupal 10/11 module for managing the JRC Cybersecurity Taxonomy with 205 taxonomy terms organized into 4 categories: Knowledge Domains, Sectors, Technologies, and Use Cases.

The original RDF vocabulary is available at https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/cybersecurity-taxonomy 

Other information can be available at https://cybersecurity-atlas.ec.europa.eu/cybersecurity-taxonomy.

## Requirements

- Drupal 10.0 or higher / Drupal 11.0 or higher
- Modules:
  - migrate
  - migrate_plus
  - taxonomy
  - views
  - text
  - link
  - options

## Installation

1. Copy this module to your Drupal installation:  
   `modules/custom/jrc_cybersecurity_taxonomy/`

2. Enable the module:
   ```bash
   drush en jrc_cybersecurity_taxonomy -y
   ```

3. Enable required migrate modules if not already enabled:
   ```bash
   drush en migrate migrate_plus -y
   ```

## Running the Migration

Import all taxonomy terms and categories:

```bash
drush migrate:import --group=jrc_cybersecurity
```

Import a specific migration:
```bash
drush migrate:import jrc_cybersecurity_taxonomy_vocabularies
drush migrate:import jrc_cybersecurity_taxonomy_fields
drush migrate:import jrc_cybersecurity_taxonomy_terms
```

## Verifying the Migration

Check migration status:
```bash
drush migrate:status --group=jrc_cybersecurity
```

View any import errors or messages:
```bash
drush migrate:messages jrc_cybersecurity_taxonomy_terms
```

## Structure

### Vocabularies (2 total)

1. **jrc_cybersecurity_taxonomy** - Main vocabulary with 205 terms
2. **jrc_cybersecurity_category** - Category vocabulary with 4 terms:
   - Knowledge Domain (165 terms)
   - Sector (15 terms)
   - Technology (17 terms)
   - Use Case (6 terms)

### Fields on Main Vocabulary

| Field | Type | Description |
|-------|------|-------------|
| field_jrc_uri | Link | URI of the concept |
| field_jrc_notation | Text | Short code or notation |
| field_jrc_description | Text Long | Detailed description |
| field_jrc_alt_labels | Text (multiple) | Alternative labels |
| field_jrc_in_schemes | Text (multiple) | Schemes the concept belongs to |
| field_jrc_broader | Entity Reference | Broader (parent) concepts |
| field_jrc_narrower | Entity Reference | Narrower (child) concepts |
| field_jrc_category_type | Entity Reference | Category type reference |

## Accessing the View

The module provides one view with 4 tabbed displays for each category:

- **Knowledge Domains**: `/cybersecurity/knowledge-domains` (165 terms)
- **Sectors**: `/cybersecurity/sectors` (15 terms)
- **Technologies**: `/cybersecurity/technologies` (17 terms)
- **Use Cases**: `/cybersecurity/use-cases` (6 terms)

All pages show a table with the following columns:
- Title (linked)
- Category Type
- URI
- Notation
- Description (trimmed to 300 characters)
- Alternative Labels
- Broader concepts
- Narrower concepts

### Admin Menu

Navigate to **Structure > Cybersecurity Taxonomy** to access:
- Overview of all terms
- Individual category views

## Rollback

To rollback and remove all import data:

```bash
drush migrate:rollback --group=jrc_cybersecurity
drush pmu jrc_cybersecurity_taxonomy -y
```

## Custom Migrate Plugins

The module includes 2 custom migrate process plugins:

### JrcUriLookup
Lookup taxonomy terms by URI instead of ID for establishing broader/narrower relationships.

### JrcCategoryFromSchemes
Determines the category type from the `in_schemes` field by mapping scheme URIs to category TIDs.

## Troubleshooting

### Import fails with "CSV file not found"
Ensure the CSV path in the migration file (`jrc_cybersecurity_taxonomy_terms.yml`) matches your installation. Update the path if necessary:
```yaml
path: modules/custom/jrc_cybersecurity_taxonomy/migrations/source/cybersecurity_taxonomy_entities.csv
```

### Broader/Narrower relationships not working
Run the migration twice to establish all relationships, as some terms may reference others that haven't been imported yet:
```bash
drush migrate:import jrc_cybersecurity_taxonomy_terms --execute-dependencies
```

## License

This module is provided as-is for managing the JRC Cybersecurity Taxonomy data.

## Author

M.Marcello Verona - COcyber Project - The Lisbon Council asbl 