{if $hasContactErrors}
    <div class="crm-summary-row">
        <div class="crm-label">
            Contact Sync Errors
        </div>
        <div class="crm-content">
            Contact <span class='error'>sync error</span> with Myob <a href='#' class='helpicon error myoberror-info' data-myoberrorid='{$accountContactId}'></a>
        </div>
    </div>
{/if}