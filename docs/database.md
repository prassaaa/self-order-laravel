# Database Schema Documentation

This document describes the database schema for the Self Order Laravel application.

## Overview

The database is designed to support a complete self-ordering system with the following main entities:
- Users and authentication
- Menu categories and items
- Orders and order items
- Payments and transactions
- Roles and permissions

## Database Diagram

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│    users    │    │ categories  │    │    menus    │
├─────────────┤    ├─────────────┤    ├─────────────┤
│ id (PK)     │    │ id (PK)     │    │ id (PK)     │
│ name        │    │ name        │    │ category_id │
│ email       │    │ slug        │    │ name        │
│ password    │    │ description │    │ description │
│ ...         │    │ image       │    │ price       │
└─────────────┘    │ is_active   │    │ image       │
                   │ sort_order  │    │ is_available│
                   └─────────────┘    │ sort_order  │
                                      └─────────────┘
                                             │
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   orders    │    │ order_items │────┘
├─────────────┤    ├─────────────┤
│ id (PK)     │────│ id (PK)     │
│ order_number│    │ order_id    │
│ table_number│    │ menu_id     │
│ customer_*  │    │ quantity    │
│ status      │    │ price       │
│ payment_*   │    │ subtotal    │
│ total_amount│    │ notes       │
│ notes       │    └─────────────┘
└─────────────┘
       │
┌─────────────┐
│  payments   │
├─────────────┤
│ id (PK)     │
│ order_id    │────┘
│ amount      │
│ method      │
│ status      │
│ transaction_id
│ processed_by│
│ processed_at│
└─────────────┘
```

## Tables

### users
Stores user accounts for admin and kasir roles.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY, AUTO_INCREMENT | Unique user identifier |
| name | varchar(255) | NOT NULL | User's full name |
| email | varchar(255) | NOT NULL, UNIQUE | User's email address |
| email_verified_at | timestamp | NULL | Email verification timestamp |
| password | varchar(255) | NOT NULL | Hashed password |
| avatar | varchar(255) | NULL | Profile avatar path |
| remember_token | varchar(100) | NULL | Remember me token |
| created_at | timestamp | NULL | Record creation time |
| updated_at | timestamp | NULL | Record last update time |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (email)
- INDEX (email_verified_at)

### categories
Stores menu categories for organizing menu items.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY, AUTO_INCREMENT | Unique category identifier |
| name | varchar(255) | NOT NULL | Category name |
| slug | varchar(255) | NOT NULL, UNIQUE | URL-friendly category identifier |
| description | text | NULL | Category description |
| image | varchar(255) | NULL | Category image path |
| is_active | boolean | NOT NULL, DEFAULT 1 | Category visibility status |
| sort_order | int | NOT NULL, DEFAULT 0 | Display order |
| created_at | timestamp | NULL | Record creation time |
| updated_at | timestamp | NULL | Record last update time |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (slug)
- INDEX (is_active, sort_order)

### menus
Stores menu items available for ordering.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY, AUTO_INCREMENT | Unique menu identifier |
| category_id | bigint | NOT NULL, FOREIGN KEY | Reference to categories.id |
| name | varchar(255) | NOT NULL | Menu item name |
| description | text | NULL | Menu item description |
| price | decimal(10,2) | NOT NULL | Item price |
| image | varchar(255) | NULL | Menu item image path |
| is_available | boolean | NOT NULL, DEFAULT 1 | Item availability status |
| sort_order | int | NOT NULL, DEFAULT 0 | Display order within category |
| created_at | timestamp | NULL | Record creation time |
| updated_at | timestamp | NULL | Record last update time |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (category_id) REFERENCES categories(id)
- INDEX (category_id, is_available)
- INDEX (is_available, sort_order)
- INDEX (price)

### orders
Stores customer orders.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY, AUTO_INCREMENT | Unique order identifier |
| order_number | varchar(255) | NOT NULL, UNIQUE | Human-readable order number |
| table_number | varchar(50) | NULL | Table number for dine-in orders |
| customer_name | varchar(255) | NULL | Customer name |
| customer_phone | varchar(20) | NULL | Customer phone number |
| status | enum | NOT NULL, DEFAULT 'pending' | Order status |
| payment_status | enum | NOT NULL, DEFAULT 'pending' | Payment status |
| total_amount | decimal(10,2) | NOT NULL, DEFAULT 0 | Total order amount |
| notes | text | NULL | Special instructions |
| created_at | timestamp | NULL | Record creation time |
| updated_at | timestamp | NULL | Record last update time |

**Status Values:**
- Order Status: pending, confirmed, preparing, ready, completed, cancelled
- Payment Status: pending, paid, failed, refunded

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE KEY (order_number)
- INDEX (status, created_at)
- INDEX (payment_status, created_at)
- INDEX (table_number)
- INDEX (customer_phone)
- INDEX (created_at)

### order_items
Stores individual items within an order.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY, AUTO_INCREMENT | Unique order item identifier |
| order_id | bigint | NOT NULL, FOREIGN KEY | Reference to orders.id |
| menu_id | bigint | NOT NULL, FOREIGN KEY | Reference to menus.id |
| quantity | int | NOT NULL | Item quantity |
| price | decimal(10,2) | NOT NULL | Item price at time of order |
| subtotal | decimal(10,2) | NOT NULL | Calculated subtotal (quantity × price) |
| notes | text | NULL | Item-specific notes |
| created_at | timestamp | NULL | Record creation time |
| updated_at | timestamp | NULL | Record last update time |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
- FOREIGN KEY (menu_id) REFERENCES menus(id)
- INDEX (order_id, menu_id)
- INDEX (menu_id)

### payments
Stores payment transactions for orders.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY, AUTO_INCREMENT | Unique payment identifier |
| order_id | bigint | NOT NULL, FOREIGN KEY | Reference to orders.id |
| amount | decimal(10,2) | NOT NULL | Payment amount |
| method | enum | NOT NULL | Payment method |
| status | enum | NOT NULL, DEFAULT 'pending' | Payment status |
| transaction_id | varchar(255) | NULL | External transaction ID |
| notes | text | NULL | Payment notes |
| processed_by | bigint | NULL, FOREIGN KEY | Reference to users.id |
| processed_at | timestamp | NULL | Payment processing time |
| created_at | timestamp | NULL | Record creation time |
| updated_at | timestamp | NULL | Record last update time |

**Method Values:** cash, qris, bank_transfer, e_wallet
**Status Values:** pending, completed, failed, refunded

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (order_id) REFERENCES orders(id)
- FOREIGN KEY (processed_by) REFERENCES users(id)
- INDEX (order_id, status)
- INDEX (status, processed_at)
- INDEX (method)
- INDEX (processed_by)
- INDEX (processed_at)

### model_has_roles
Stores user role assignments (Spatie Laravel Permission).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| role_id | bigint | NOT NULL, FOREIGN KEY | Reference to roles.id |
| model_type | varchar(255) | NOT NULL | Model class name |
| model_id | bigint | NOT NULL | Model instance ID |

### roles
Stores available roles (Spatie Laravel Permission).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY, AUTO_INCREMENT | Unique role identifier |
| name | varchar(255) | NOT NULL | Role name |
| guard_name | varchar(255) | NOT NULL | Guard name |
| created_at | timestamp | NULL | Record creation time |
| updated_at | timestamp | NULL | Record last update time |

### permissions
Stores available permissions (Spatie Laravel Permission).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY, AUTO_INCREMENT | Unique permission identifier |
| name | varchar(255) | NOT NULL | Permission name |
| guard_name | varchar(255) | NOT NULL | Guard name |
| created_at | timestamp | NULL | Record creation time |
| updated_at | timestamp | NULL | Record last update time |

## Relationships

### One-to-Many Relationships
- `categories` → `menus` (One category has many menus)
- `orders` → `order_items` (One order has many order items)
- `orders` → `payments` (One order can have multiple payments)
- `menus` → `order_items` (One menu can be in many order items)
- `users` → `payments` (One user can process many payments)

### Many-to-Many Relationships
- `users` ↔ `roles` (Users can have multiple roles)
- `roles` ↔ `permissions` (Roles can have multiple permissions)

## Performance Considerations

### Indexes
The database includes strategic indexes for optimal query performance:
- Composite indexes for common filter combinations
- Foreign key indexes for join operations
- Timestamp indexes for date-based queries

### Query Optimization
- Use eager loading for relationships to avoid N+1 queries
- Implement database-level constraints for data integrity
- Use appropriate data types for optimal storage

## Data Integrity

### Foreign Key Constraints
- All foreign keys have proper constraints with appropriate cascade rules
- Order items are deleted when parent order is deleted
- Menu references are protected to maintain order history

### Validation Rules
- Email uniqueness enforced at database level
- Order numbers are unique across the system
- Enum values are constrained at database level
- Decimal precision is appropriate for currency values

## Backup and Maintenance

### Recommended Backup Strategy
- Daily full backups
- Transaction log backups every 15 minutes
- Test restore procedures monthly

### Maintenance Tasks
- Regular index maintenance and statistics updates
- Monitor query performance and optimize as needed
- Archive old orders and payments based on retention policy
