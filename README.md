# Opencart - Credit Purchase Extension
This extension works currently on Opencart v2.0.3.1 because it was the opencart I used for development. 

## How it works
The extension takes the concept of vouchers of Opencart but using 2 parameters only: description and amount. 

1. The customer can access the credit form from the Account Page. 
2. Once in the Credit section, the customer can choose the amount of credit he wish to purchase and add it to the cart.
3. Once added, the credit can be purchased from the Checkout section like any other product.
4. The system validates the items of the cart checking for credits in order to avoid the purchase of credits using credits (customer transactions).
5. Once the order has been approved and completed, the amount of credits will be added to the customer transaction. 
6. From the Administration, the credits can be added/edited in orders like any other product or voucher.
7. The Min/Max parameters of credits can be set in the Store Settings: Setting -> Edit store -> Option tab.

## Final Notes
The Extension needs work with languages files for English and Spanish. Also, the extension only works in Opencart 2.0.3.1 for now.

## Screenshots
### Access the Credit section from Customer's Account page:
![Credit Purchase](http://i.imgur.com/wSaW20X.jpg)

### Credit Section of the Catalog
![Credit Purchase](http://i.imgur.com/0tbpCVK.jpg)

### Credit item added to the cart
![Credit Purchase](http://i.imgur.com/qFFJlS0.jpg)

### Adding credits to order from Administration:
![Credit Purchase](http://i.imgur.com/tq78jRx.jpg)

### Configuring Min/Max values of Credits in Admin Store Settings Option:
![Credit Purchase](http://i.imgur.com/IMmuWl6.jpg)

