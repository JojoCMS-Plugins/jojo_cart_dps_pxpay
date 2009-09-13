A payment plugin to integrate DPS with jojo_cart.

You will need:
-The jojo_cart plugin, and one of the checkout plugins.
-A web server with CURL installed.
-a New Zealand merchant account from your bank.
-A DPS account. www.paymentexpress.com
-A DPS Developer account if you wish to perform text transactions (optional, but recommended).

Install the plugin as normal. DPS will proivide you with a username / password (key). Add these to the Jojo options.

Note This plugin will not work if your shopping cart URL contains a tilde (~) character - DPS will strip this from the URL and the transaction will fail. I have raised this issue with them, so hopefully a fix is forthcoming.