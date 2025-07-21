# Inventory Management

This guide covers all aspects of inventory management in the Spa & Salon Management Software, including product management, inventory tracking, and reporting.

## Product Management

### Adding New Products
1. Navigate to Inventory > Products
2. Click "Add New Product" button
3. Enter product information:
   - Product name (required)
   - SKU/Barcode (required)
   - Brand (required)
   - Category (required)
   - Description
   - Size/Volume
   - Cost price (required)
   - Retail price (required)
   - Product image
4. Set inventory options:
   - Initial stock quantity
   - Reorder point
   - Preferred vendor
   - Location in store
5. Click "Create Product"

### Editing Product Information
1. Navigate to Inventory > Products
2. Find the product you want to edit
3. Click the "Edit" button
4. Update product information
5. Click "Save Changes"

### Product Categories
Organize products into logical groups:
1. Navigate to Inventory > Categories
2. Click "Add Category" button
3. Enter category details:
   - Category name
   - Description
   - Parent category (if applicable)
   - Display order
4. Click "Create Category"
5. Assign products to categories:
   - Edit a product
   - Select category from dropdown
   - Save changes

### Product Pricing
Set and update product prices:
1. Navigate to Inventory > Products
2. Select product(s) to update
3. Click "Update Pricing"
4. Choose update method:
   - Set fixed price
   - Apply percentage increase/decrease
   - Update markup based on cost
5. Enter new pricing information
6. Preview changes
7. Apply updates

## Inventory Tracking

### Stock Management
Track product quantities:
1. Navigate to Inventory > Stock Levels
2. View current inventory levels for all products
3. Filter by:
   - Category
   - Brand
   - Stock status (in stock, low stock, out of stock)
   - Location (for multi-location businesses)
4. Sort by quantity, name, or SKU

### Receiving Inventory
Record new inventory arrivals:
1. Navigate to Inventory > Receive Stock
2. Click "New Inventory Receipt"
3. Select vendor
4. Enter purchase order number (if applicable)
5. Add products:
   - Scan barcode or search by name/SKU
   - Enter quantity received
   - Enter cost price (if different from default)
   - Check for damaged items
6. Upload vendor invoice (optional)
7. Add notes if needed
8. Click "Complete Receipt"
9. System automatically updates inventory levels

### Stock Adjustments
Correct inventory discrepancies:
1. Navigate to Inventory > Adjustments
2. Click "New Adjustment"
3. Select adjustment type:
   - Inventory count correction
   - Damaged/expired product
   - Internal use
   - Gift/sample
   - Theft/loss
4. Add products and quantities
5. Enter reason for adjustment
6. Add supporting documentation if needed
7. Submit adjustment
8. System updates inventory levels

### Product Transfers
Move inventory between locations:
1. Navigate to Inventory > Transfers
2. Click "New Transfer"
3. Select source and destination locations
4. Add products and quantities to transfer
5. Schedule transfer date
6. Generate transfer document
7. Mark as "In Transit" when shipped
8. Recipient confirms receipt
9. System updates inventory at both locations

## Inventory Alerts

### Low Stock Alerts
Set up automatic notifications:
1. Navigate to Settings > Inventory > Alerts
2. Configure low stock thresholds:
   - Global default percentage
   - Category-specific thresholds
   - Individual product overrides
3. Set notification preferences:
   - In-app alerts
   - Email notifications
   - Frequency of alerts
4. Save alert settings

### Viewing Current Alerts
Monitor inventory status:
1. Navigate to Inventory > Alerts
2. View all current alerts:
   - Low stock items
   - Out of stock items
   - Excess inventory
   - Expiring products
3. Take action directly from alerts:
   - Create purchase orders
   - Adjust reorder points
   - Transfer stock from other locations

### Reorder Recommendations
Get smart purchasing suggestions:
1. Navigate to Inventory > Reorder List
2. System displays products below reorder point
3. View recommendations based on:
   - Current stock level
   - Historical sales velocity
   - Lead time from vendor
   - Minimum order quantities
4. Select products to reorder
5. Generate purchase orders automatically

## Inventory Reports

### Inventory Valuation
Track the value of your inventory:
1. Navigate to Reports > Inventory > Valuation
2. Set report parameters:
   - As of date
   - Location(s)
   - Categories
3. View valuation metrics:
   - Total inventory value at cost
   - Total inventory value at retail
   - Potential profit margin
   - Value by category/brand
4. Export report for accounting purposes

### Product Performance
Analyze how products are selling:
1. Navigate to Reports > Inventory > Product Performance
2. Set date range
3. View performance metrics:
   - Units sold
   - Revenue generated
   - Profit margin
   - Turn rate
   - Days of supply
4. Identify top and bottom performers
5. Use data to inform purchasing decisions

### Shrinkage Reports
Track inventory loss:
1. Navigate to Reports > Inventory > Shrinkage
2. Set date range
3. View shrinkage data:
   - Total value of lost inventory
   - Breakdown by reason code
   - Comparison to previous periods
   - Shrinkage as percentage of sales
4. Identify patterns or problem areas

### Inventory Turnover
Measure inventory efficiency:
1. Navigate to Reports > Inventory > Turnover
2. Set date range
3. View turnover metrics:
   - Inventory turnover ratio
   - Days of inventory on hand
   - Slow-moving inventory
   - Dead stock (no sales in specified period)
4. Analyze by category, brand, or product

## Inventory Counts

### Scheduling Inventory Counts
Plan regular inventory verification:
1. Navigate to Inventory > Counts
2. Click "Schedule Count"
3. Select count type:
   - Full inventory count
   - Cycle count (partial inventory)
   - Spot check
4. Set count date and time
5. Assign staff to count
6. Select products/categories to count
7. Generate count sheets

### Performing Counts
Execute inventory verification:
1. Navigate to Inventory > Counts > Active Counts
2. Select the scheduled count
3. Click "Start Count"
4. Enter counted quantities:
   - Scan products with mobile device
   - Enter quantities manually
   - Note damaged or expired items
5. Save progress as you go
6. Mark sections as complete

### Reconciling Counts
Process count results:
1. After completing count, click "Reconcile"
2. System shows discrepancies between expected and counted quantities
3. Review each discrepancy
4. Choose action:
   - Accept counted quantity (creates adjustment)
   - Investigate further
   - Recount item
5. Add notes explaining significant variances
6. Finalize count
7. System updates inventory levels

## Best Practices for Inventory Management

- Conduct regular inventory counts (full counts quarterly, cycle counts monthly)
- Keep receiving and adjustment documentation for audit purposes
- Train staff on proper inventory procedures
- Regularly review slow-moving inventory
- Set appropriate reorder points based on lead time and usage
- Use barcode scanning whenever possible to reduce errors
- Analyze inventory reports monthly to identify trends
- Maintain accurate product costs for proper valuation
- Secure high-value inventory items
- Clean up product database regularly (merge duplicates, remove discontinued items)

## Implementation Roadmap

The following features described in this documentation require implementation or enhancement:

### Inventory Tracking
1. **Receiving Inventory**
   - Develop dedicated UI for recording new inventory arrivals
   - Implement barcode scanning functionality
   - Create vendor invoice upload and storage
   - Add purchase order integration

2. **Stock Adjustments**
   - Implement comprehensive adjustment types (damage, expiry, internal use, gifts, theft)
   - Add supporting documentation upload for adjustments
   - Create audit trail for all adjustments

3. **Product Transfers**
   - Build transfer management system for multi-location businesses
   - Implement transfer status tracking (pending, in transit, received)
   - Create transfer documentation generation

### Inventory Alerts
1. **Alert Configuration**
   - Develop UI for configuring global, category, and product-specific thresholds
   - Implement notification preferences (in-app, email)
   - Add alert frequency settings

2. **Reorder Recommendations**
   - Implement sales velocity analysis
   - Create smart reorder suggestions based on historical data
   - Add lead time and minimum order quantity considerations
   - Build automatic purchase order generation

### Inventory Reports
1. **Inventory Valuation**
   - Develop comprehensive valuation reporting
   - Implement cost vs. retail valuation options
   - Add category and brand breakdown analysis

2. **Product Performance**
   - Create detailed performance metrics (units sold, revenue, profit margin)
   - Implement turn rate and days of supply calculations
   - Add top/bottom performer identification

3. **Shrinkage Reports**
   - Develop shrinkage tracking by reason code
   - Implement period comparison reporting
   - Add shrinkage as percentage of sales calculation

4. **Inventory Turnover**
   - Build turnover ratio calculations
   - Implement slow-moving inventory identification
   - Create dead stock reporting

### Inventory Counts
1. **Count Scheduling**
   - Develop count scheduling system
   - Implement different count types (full, cycle, spot)
   - Add staff assignment functionality
   - Create count sheet generation

2. **Count Execution**
   - Build mobile-friendly counting interface
   - Implement barcode scanning for counts
   - Add progress tracking and section completion

3. **Count Reconciliation**
   - Develop discrepancy identification system
   - Implement reconciliation workflow
   - Add variance documentation and approval process
