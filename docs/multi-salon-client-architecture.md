# Multi-Salon Client Architecture Guide

## Overview
This document clarifies how clients can patronize multiple salons that each have their own Faxtina subscription, and how the system handles this multi-tenant scenario.

## Core Architecture

### **Multi-Tenant Design**
Faxtina operates as a **multi-tenant SaaS platform** where each salon/company has:
- **Independent subscription** (separate billing)
- **Isolated client database** (clients don't cross company boundaries)
- **Separate staff management** (staff can't see other companies)
- **Company-specific email templates** (welcome emails per salon)

## Relationship Structure

### **1. Company = Salon = Tenant**
Each salon that subscribes to Faxtina is represented as:
```
Company (Salon A)
├── Subscription: Basic Plan ($99/month)
├── Staff: 5 employees
├── Clients: 500+ registered clients
└── Email Templates: Custom welcome emails

Company (Salon B)
├── Subscription: Premium Plan ($199/month)
├── Staff: 12 employees
├── Clients: 1200+ registered clients
└── Email Templates: Different welcome emails
```

### **2. Client Isolation**
**Key Principle**: A client record is **scoped to exactly one company**

```sql
-- Client records are company-specific
clients (
    id,
    company_id,  -- FK to companies.id
    first_name,
    last_name,
    email,
    ...
)

-- Jane Doe at Salon A
id: 1, company_id: 1, email: "jane@email.com", first_name: "Jane", last_name: "Doe"

-- Jane Doe at Salon B (SEPARATE RECORD)
id: 2, company_id: 2, email: "jane@email.com", first_name: "Jane", last_name: "Doe"
```

## How Clients Patronize Multiple Salons

### **Scenario: Jane wants services at multiple salons**

#### **Option 1: Separate Client Records (Current Design)**
```
Salon A (Downtown Spa)
├── Client Record: Jane Doe (ID: 1)
├── Services: Hair, Nails
├── History: 15 appointments
└── Loyalty Points: 250 points

Salon B (Uptown Beauty)
├── Client Record: Jane Doe (ID: 2) 
├── Services: Massage, Facials
├── History: 8 appointments
└── Loyalty Points: 120 points
```

**Advantages:**
- Complete data isolation between salons
- Independent loyalty programs
- Separate booking history
- Privacy between salons
- Simpler billing for salons

**Disadvantages:**
- Client has multiple profiles
- No shared history across salons
- Separate login credentials (if applicable)

#### **Option 2: Future Enhancement - Cross-Company Client Linking**
```
clients (
    id,
    company_id,
    master_client_id,  -- Links to master record
    ...
)

master_clients (
    id,
    email,  -- Unique across all companies
    phone,
    universal_profile_data
)
```

## Staff Relationships

### **Staff can work at multiple salons** (company_user pivot)
```sql
company_user (
    user_id,
    company_id,
    is_primary,
    role,  -- 'admin', 'staff', 'manager'
    ...
)

-- Sarah works at both salons
user_id: 100, company_id: 1, role: 'stylist', is_primary: true
user_id: 100, company_id: 2, role: 'stylist', is_primary: false
```

### **Staff Access Control**
- **Salon A staff** can only see **Salon A clients**
- **Cross-company staff** can switch between companies
- **Data isolation** maintained at database level

## Email Template Architecture

### **Company-Specific Templates**
```sql
email_campaigns (
    id,
    company_id,  -- NULL = global template
    type: 'welcome',
    subject: 'Welcome to {{company_name}}!',
    content: 'Dear {{client_first_name}}...',
    ...
)

-- Salon A's welcome email
id: 1, company_id: 1, subject: 'Welcome to Downtown Spa!'

-- Salon B's welcome email  
id: 2, company_id: 2, subject: 'Welcome to Uptown Beauty!'
```

## Implementation Examples

### **Client Registration per Salon**
```php
// Client registers at Salon A
POST /api/clients/register
{
    "first_name": "Jane",
    "last_name": "Doe", 
    "email": "jane@email.com",
    "company_id": 1  // Salon A
}

// Client registers at Salon B (separate record)
POST /api/clients/register  
{
    "first_name": "Jane",
    "last_name": "Doe",
    "email": "jane@email.com", 
    "company_id": 2  // Salon B
}
```

### **Staff Working Multiple Locations**
```php
// Staff member at Salon A
$user->companies()->attach(1, ['role' => 'stylist', 'is_primary' => true]);

// Same staff member also works at Salon B
$user->companies()->attach(2, ['role' => 'stylist', 'is_primary' => false]);
```

## Database Schema Summary

### **Companies (Salons)**
- **Each company = separate subscription**
- **Independent client base**
- **Separate billing**
- **Isolated data**

### **Clients**
- **Scoped to single company**
- **No cross-company data sharing**
- **Separate records per salon**

### **Users (Staff)**
- **Can belong to multiple companies**
- **Role-based access per company**
- **Company switching capability**

## Business Rules

1. **Client Privacy**: Salon A cannot see Salon B's client data
2. **Subscription Independence**: Each salon pays separately
3. **Data Isolation**: Complete separation between companies
4. **Staff Flexibility**: Stylists can work at multiple locations
5. **Email Personalization**: Welcome emails are salon-specific

## Future Considerations

### **Potential Enhancements**
- **Universal client ID** for cross-salon recognition
- **Shared loyalty programs** between partnered salons
- **Staff scheduling** across multiple locations
- **Client consent** for data sharing between salons
- **Corporate chains** with shared client base

### **Current Limitations**
- Client must register separately at each salon
- No shared appointment history
- Separate loyalty programs
- Multiple client profiles per person

## Summary

**Current Design**: Each salon operates as an independent tenant
- ✅ Complete data isolation
- ✅ Separate billing
- ✅ Independent client management
- ✅ Company-specific welcome emails

**Client Experience**: Jane Doe has separate profiles at each salon she visits, ensuring privacy and data separation between competing businesses.
