# Collector-s-market

## Database and system design

The initial database schema and design documents for the model and trading-card store are available here:

- [MySQL schema](database/schema.sql) - creates the `model_store` database, tables, foreign keys, and initial categories/roles/permissions.
- [System design](docs/system-design.md) - core architecture, access control, and product/category administration design.
- [Admin UI prototype](docs/admin-products-prototype.html) - clickable, browser-only screens for dashboard, products, categories, orders, customers, reviews, and settings.
- [Data architecture and ERD](.github/modernize/assessment/engines/facts/data-architecture.md) - proposed entity model and Mermaid ER diagram.

### Create the database

Requires MySQL 8.0 or a compatible MariaDB version. From the repository root, run:

```sh
mysql -u root -p < database/schema.sql
```

The script does not create an administrator account or include a default password. Create the first admin securely in the application, and keep real database credentials in an untracked `.env` file.