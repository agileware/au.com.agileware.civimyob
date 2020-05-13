# MYOB AccountRight integration for CiviCRM

CiviCRM Extension integrating [CiviCRM](https://civicrm.org) with
[MYOB AccountRight](https://www.myob.com/au/accounting-software/accountright).
CiviCRM Contacts and Contributions are pushed to MYOB AccountRight,
QuickBooks Invoices are pulled into CiviCRM, updating the Contribution status.
Synchronise all things!

Integration of the two systems saves you time by not having to do repetitive
data entry in your accounting system and CiviCRM. This extension does the work
for you!

## Dependencies
1. Requires CiviCRM extension [nz.co.fuzion.accountsync](https://github.com/eileenmcnaughton/nz.co.fuzion.accountsync)

## Installation

1. Ensure CiviCRM [nz.co.fuzion.accountsync](https://github.com/eileenmcnaughton/nz.co.fuzion.accountsync) extension is installed.
1. Download the repository(whole extension folder) to your CiviCRM dedicated extension directory (available at 'System Settings / Resource URLs').
1. Then go to 'System Settings / Extensions', where you should be able to see the 'MYOB AccountRight Integration' extension listed.
1. Enable the extension
1. 'MYOB' will now be available in the 'Administer' menu.

## Configuration

### Register App

1. To use this extension you will first need to enable the developer access on MYOB.
1. Once the developer access is enabled, Click on [Developer](https://my.myob.com.au/Bd/pages/DevAppList.aspx) tab
1. Click on [Register App](https://my.myob.com.au/Bd/pages/DevAppEdit.aspx) button
1. Add any relevant app name. (e.g. CiviCRM App)
1. **Redirect Uri** must be set to the **MYOB Settings page**. So if your website URL is [http://myorganisation.org](http://myorganisation.org) then the redirect URL will be [http://myorganisation.org/civicrm/myob/settings](http://myorganisation.org/civicrm/myob/settings) (If the redirect URL is wrong extension won't work)
1. You can leave the rest of the fields and click on **Register App** button.

Above steps will create an app in MYOB. Credentials of the created app will be displayed on the same page in a table. Again make sure the redirect Uri is correct, if not edit the app and add the correct redirect uri.

Value of **Key** and **Secret** will be required in next step.

### Add extension settings
1. Open 'Administer > MYOB > MYOB Settings' page to configure the extension
1. If you're testing the extension with Sandbox account add **Administrator** in Username field other add your MYOB accountright username.
1. Again, If you're testing the eextension with Sanbod account keep the password field **blank** otherwise add your MYOB accountright password.
1. Copy the **Key** of created app from [MYOB Developer Dashboard](https://my.myob.com.au/Bd/pages/DevAppList.aspx) and add it into **API Key** field on extension  settings page.
1. Copy the **Secret** of created app from [MYOB Developer Dashboard](https://my.myob.com.au/Bd/pages/DevAppList.aspx) and add it into **API Secret** field on extension  settings page.
1. Copy the **Redirect Uri** of created app from [MYOB Developer Dashboard](https://my.myob.com.au/Bd/pages/DevAppList.aspx) and add it into **Redirect Uri** field on extension settings page.
1. Select Yes/No in **Is Sandbox ?** field accordingly.
1. Click **Authorize** button.

    If you're adding this app for first time. You will need to authorise the CiviCRM to access the following from your MYOB Account:
    - AccountRight company files
    - Essentials Accounting

    To do that, Click on **Allow Access**.
    
    If you've already added this app earlier and configuring it for the second time, MYOB won't ask the for access permissions.

1. Now a new **Company** field is added to the bottom of the form. All the invoices and contacts will be synced to selected company file.
   
   If you can't see your company file in the list, Click on **refresh list** link under the field. 
   
   Check the value of **API Key**, **API Secret** and **Redirect Uri** if you see any errors at the top of the form or don't see any company files in the list.
   
1. Click on **Select company**
1. Once the company is selected, all the configurable fields are visible on the form along with three **MYOB** configurable fields.

    - TaxCode
    - Freight TaxCode
    - Account
    
1. Select the above fields from the list and click on **Submit** button.

**MYOB AccountRight Integration** extension is now configured and will sync the contacts and invoices according to the settings.

# About the Authors

This CiviCRM extension was developed by the team at
[Agileware](https://agileware.com.au).

[Agileware](https://agileware.com.au) provide a range of CiviCRM services
including:

  * CiviCRM migration
  * CiviCRM integration
  * CiviCRM extension development
  * CiviCRM support
  * CiviCRM hosting
  * CiviCRM remote training services

Support your Australian [CiviCRM](https://civicrm.org) developers, [contact
Agileware](https://agileware.com.au/contact) today!


![Agileware](logo/agileware-logo.png)