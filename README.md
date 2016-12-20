# Magento2 Product Most Viewed
Extension displays the top most viewed products for your stores.

### Home page
<img alt="Magento2 ProductMostViewed" src="https://karliuka.github.io/m2/product-most-viewed/home.png" style="width:100%"/>
### Category page
<img alt="Magento2 ProductMostOrdered" src="https://karliuka.github.io/m2/product-most-viewed/category.png" style="width:100%"/>
## Install with Composer as you go

1. Go to Magento2 root folder

2. Enter following commands to install module:

    ```bash
    composer require faonni/module-product-most-viewed
    ```
   Wait while dependencies are updated.

3. Enter following commands to enable module:

    ```bash
	php bin/magento setup:upgrade
	php bin/magento setup:static-content:deploy
    ```
## Display and configuration
A shortcode to Homepage and to other CMS pages or CMS blocks.
    ```bash
{{block class='Faonni\ProductMostViewed\Block\ProductList' 
		template='Faonni_ProductMostViewed::product/list/items.phtml' 
		title='Most Viewed Products' 
		num_products='6'
}}
    ```
A Layout Update XML to all categories.
    ```xml
<referenceBlock name="catalog.product.most.viewed">
	<action method="setTitle">
		<argument name="title" xsi:type="string" translate="true">Most Viewed Products of Category</argument>
	</action>
	<action method="setNumProducts">
		<argument name="num_products" xsi:type="string">6</argument>
	</action>	
</referenceBlock>
    ```
    
* [Magento2 Product Most Ordered](https://github.com/karliuka/m2.ProductMostOrdered)
