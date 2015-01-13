bitshares/xcartgold-plugin
=======================

# Installation

1. Copy these files into your xcart root directory
2. Copy Bitshares Checkout (https://github.com/sidhujag/bitsharescheckout) files into your xcart root directory, overwrite any existing files.
3. Enable REST API plugin from marketplace with a read/write api key. This API is needed to communicate to and from the XCart system.

# Configuration

1. In your XCart admin panel, go to Settings > Payment Methods > Payment Gateways.
2. Change Your Country to All Countries, select Bitshares and click Add.
3. Click Payment Methods tab, check the box next to Bitshares and click Apply Changes.
4. Fill out config.php with appropriate information and configure Bitshares Checkout
    - See the readme at https://github.com/sidhujag/bitsharescheckout
    - Set $apiKey to the read/write key you set in installation step #3
    
*Note: Turn off any external HTML minification as XCart uses SSI includes to pass dynamic data from PHP to HTML sections. HTML minifiers strip this information out. CDN's such as MaxCDN and Cloudflare will have HTML minification on by default so make sure to check and turn it off. JS/CSS minification and optimization is OK.  

# Usage

When a shopper chooses the Bitshares payment method, they will be redirected to Bitshares Checkout where they will pay an invoice.  Bitshares Checkout will then notify your Xcart system that the order was paid for.  The customer will be redirected back to your store.  

The order status in the admin panel will be "Processed" if payment has been confirmed. 


# Support

## Bitshares Support

* [GitHub Issues](https://github.com/sidhujag/bitshares-xcart/issues)
  * Open an issue if you are having issues with this plugin.


## X-Cart Support

* [Homepage](http://www.x-cart.com/ecommerce-software.html)
* [Documentation](http://kb.x-cart.com/display/XDD/Definitive+guide)
* [Support Forums](http://forum.x-cart.com)

# Contribute

To contribute to this project, please fork and submit a pull request.

# License

The MIT License (MIT)

Copyright (c) 2011-2014 Bitshares

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
