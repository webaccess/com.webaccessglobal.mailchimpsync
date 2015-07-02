{* this template is used to add/edit the API Key for a contact *}

<div class="form-item">
        <div class="crm-block crm-form-block crm-cividesk-api-form-block">
            
            <table class="form-layout-compressed">
                <tr class="crm-apikey-form-block">
                    <td class="label">{$form.api_key.label}</td>
                    <td>
                        {$form.api_key.html}
                    </td>  
                </tr>
                <tr class="crm-apikey-form-block">
                    <td class="label"></td>
                    <td><div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div></td>
                </tr>
            </table>
        </div>
</div>                     