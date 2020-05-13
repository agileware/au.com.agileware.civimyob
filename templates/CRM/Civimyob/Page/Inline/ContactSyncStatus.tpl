<div class="crm-summary-row">
    <div class="crm-label">
        Myob Sync Status
    </div>
    <div class="crm-content">
        {if $syncStatus == 0}
            <a href='#' id='myob-sync' data-contact-id={$contactID}>Queue Sync to Myob</a>
        {elseif $syncStatus == 1}
            Contact is synced with Myob
        {elseif $syncStatus == 2}
            Contact is queued for sync with Myob
        {/if}
    </div>

    {literal}

        <script type="text/javascript">
            cj('#myob-sync').click(function( event) {
                event.preventDefault();
                CRM.api('account_contact', 'create',{
                    'contact_id' : cj(this).data('contact-id'),
                    'plugin' : 'myob',
                    'accounts_needs_update' : 1,
                });
                cj(this).replaceWith('<p>Contact is queued for sync with Myob</p>');
            });
        </script>

    {/literal}
</div>