{*
* Fetchr
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* It is also available through the world-wide-web at this URL:
* https://fetchr.zendesk.com/hc/en-us/categories/200522821-Downloads
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to ws@fetchr.us so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade Fetchr PrestaShop Module to newer
* versions in the future. If you wish to customize Fetchr PrestaShop Module (Fetchr Shiphappy) for your
* needs please refer to http://www.fetchr.us for more information.
*
* @author     Fetchr.us
* @package    Fetchr Shiphappy
* Used in pusing order from PrestaShop Store to Fetchr
* @copyright  Copyright (c) 2015 Fetchr (http://www.fetchr.us)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*} 

{$message}
        <form method="post">
                <input type="hidden" name="fetchr_liveurl" value="http://menavip.com">
                <input type="hidden" name="fetchr_stagingurl" value="http://dev.menavip.com">
                <fieldset class="space">
                <label>Username</label>
                <input type="text" name="fetchr_username" value="{$fetchr_username}" class="input" required="required"><br>
                <label>Password</label>
                <input type="text" name="fetchr_password" value="{$fetchr_password}" class="input" required="required"><br>
                <label>Service Type</label>
                <select name="fetchr_servcie_type">
                <option value="delivery" {if $fetchr_servcie_type== 'delivery'} selected="selected" {/if}>Delivery Only</option>
                <option value="fulfilment" {if $fetchr_servcie_type== 'fulfilment'} selected="selected" {/if}>Fulfilment + Delivery</option>
                </select><br>
                <label>Account Type</label>
                <select name="fetchr_account_type">
                <option value="staging" {if $fetchr_account_type == 'staging'} selected="selected" {/if}>Staging</option>
                <option value="live"  {if $fetchr_account_type == 'live'} selected="selected" {/if}>Live</option>
                </select><br>
                <label>&nbsp;</label>
                <input id="submit_{$module_name}" name="submit_{$module_name}" type="submit" value="Save" class="button" />
                <legend><img src="http://fetchr.us/wp-content/uploads/2015/04/fetchrlogo_white.png" width:"50" height="30" style="background-color:#ff7b47;padding:10px;"></legend>
                </fieldset>
        </form>
