{if $hasInvoiceErrors}
    <div class="crm-summary-row">
        <div class="crm-label">
            Contact Sync Errors
        </div>
        <div class="crm-content">
            {$erroredInvoices} Contribution <span class='error'>not synced</span> with Myob <a href='#' class='helpicon error myoberror-invoice-info' data-myoberrorid='{$contactID}'></a>
        </div>
    </div>
{/if}