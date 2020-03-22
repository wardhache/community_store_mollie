# community_store_mollie

Mollie payments for Community Store for Concrete5

https://docs.mollie.com/reference/v2/payments-api


## Requirements

The `community_store` package for Concrete5. (https://github.com/concrete5-community-store/community_store)

## Setup

### Mollie Dashboard

1) Create an account (https://www.mollie.com/dashboard/signup?lang=en)
2) In the Mollie Dashboard, go to "Settings" => "Website Profiles"
3) Select or create your profile.
3) Enable the needed payment methods for your store.
4) Go to "Developers" => "Api Keys"
5) Copy the needed API key (you can start experimenting using the test api key).

### Community Store 

1) Under "Store" => "Settings" in the dashboard, go to the section "Payments".
2) Enable Mollie and fill in the API key.
3) Save the settings.

## Questions

### What if I change my payment methods?

When you changed your payment methods within the Mollie dashboard,
it will not automatically be changed in your store.
To make this happen, you have to follow these simple steps:

1) Under "Store" => "Settings", go to the section "Payments".
2) In the Mollie panel, click on the button "View Mollie Payment Methods".
3) On that page, click on the blue "Rescan" button.

#### NOTICE

This package has been tested with Community Store version 2.1.10 and Concrete5 8.5.2
