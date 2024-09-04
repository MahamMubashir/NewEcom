# ShopSmart AI-Powered Ecommerce Module

## Description

ShopSmart transforms natural language queries into carefully curated shopping carts, covering a wide range of product categories. By analyzing your entire product catalog, it provides precise recommendations tailored to each consumer's unique needs. Utilizing natural language processing, ShopSmart comprehends nuanced requests to create personalized shopping experiences. Shoppers can refine their preferences and receive recommendations that match their taste and style perfectly.

## Features

- AI-powered product recommendations
- Natural language processing for nuanced queries
- Personalized shopping experiences
- Admin configuration for easy setup
- Two interactive widgets: Discover and Decide

## Requirements

- Magento 2.4.x
- PHP 7.4 or higher
- MySQL 5.7 or higher

## Installation

### Using Composer

1. **Backup Your Store:**
    ```bash
    php bin/magento setup:backup --code --media --db
    ```


3. **Enable the Extension:**
    ```bash
    php bin/magento module:enable NewEcomAI_ShopSmart
    ```

4. **Run Upgrade Scripts:**
    ```bash
    php bin/magento setup:upgrade
    ```

5. **Deploy Static Content:**
    ```bash
    php bin/magento setup:static-content:deploy
    ```

6. **Clear Cache:**
    ```bash
    php bin/magento cache:clean
    ```

### Manual Installation

1. **Backup Your Store:**
    ```bash
    php bin/magento setup:backup --code --media --db
    ```

2. **Extract and Upload:**
    - Extract the extension files.
    - Upload the extracted files to the `app/code/NewEcomAI/ShopSmart` directory.

3. **Enable the Extension:**
    ```bash
    php bin/magento module:enable NewEcomAI_ShopSmart
    ```

4. **Run Upgrade Scripts:**
    ```bash
    php bin/magento setup:upgrade
    ```

5. **Deploy Static Content:**
    ```bash
    php bin/magento setup:static-content:deploy
    ```

6. **Clear Cache:**
    ```bash
    php bin/magento cache:clean
    ```

## Configuration

Welcome to ShopSmart AI-Powered Ecommerce Module. This user manual will guide you through the installation and configuration of the Module.

### Step 1

Go to `Stores > Configuration > NewcomAi > Shopsmart`.

### Step 2

- Enable ShopSmart Module.

### Step 3

- Select Mode (Staging, Production) and add ShopSmart UserID, UserName, Password.

### Step 4

- To verify, after adding User ID, UserName, User Password, click the Validate button.

### Step 5

- Select Product Attribute to upload with initial catalog sync.

After configuring the admin settings, run the following console command for initial catalog syncing on your website:
```bash
bin/magento newecomai_shopsmart:sync_catalog_command
```

## Usage
The module results in the two widgets:

- Discover Widget on the Home page and category pages
- Decide Widget on the product pages
- Discover Widget: Provides product recommendations based on search queries.
- Decide Widget: Displays answers to questions relevant to the product.
- The widgets and blocks will be created automatically when the module is installed.

## Troubleshooting

**Clear Cache:**
```bash
php bin/magento cache:clean
```

**Re Index:**
```bash
php bin/magento index:reindex
```
For further assistance, please contact our support team.

## License

This project is licensed under the Open Source License.
# NewEcom
# NewEcom
