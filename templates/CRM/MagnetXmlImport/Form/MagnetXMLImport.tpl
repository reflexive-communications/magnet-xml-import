{crmScope extensionKey='magnet-xml-import'}
    <div class="help">{ts}Import contributions from Magnet Bank XML reports{/ts}</div>
    <div class="crm-block crm-form-block">
        <table class="form-layout">
            <tr>
                <td class="label">{$form.source.label}</td>
                <td class="content">{$form.source.html}<br/>
                    <span class="description">{ts}Contribution source{/ts}</span>
                </td>
            </tr>
            <tr>
                <td class="label">{$form.financial_type_id.label}</td>
                <td class="content">{$form.financial_type_id.html}<br/>
                    <span class="description">{ts}Financial type for imported contributions{/ts}</span>
                </td>
            </tr>
            <tr>
                <td class="label">{$form.payment_instrument_id.label}</td>
                <td class="content">{$form.payment_instrument_id.html}<br/>
                    <span class="description">{ts}Payment method for imported contributions{/ts}</span>
                </td>
            </tr>
            <tr>
                <td class="label">{$form.bank_account_custom_field.label}</td>
                <td class="content">{$form.bank_account_custom_field.html}<br/>
                    <span class="description">{ts}Custom field that holds bank account numbers{/ts}</span>
                </td>
            </tr>
            <tr>
                <td class="label">{$form.only_income.label}</td>
                <td class="content">{$form.only_income.html}<br/>
                    <span class="description">{ts}Process only income transactions?{/ts}</span>
                </td>
            </tr>
            <tr>
                <td class="label">{$form.import_file.label}</td>
                <td class="content">{$form.import_file.html}<br/>
                    <span class="description">{ts}Upload Magnet Bank XML report file{/ts}</span>
                </td>
            </tr>
        </table>
        <div class="crm-submit-buttons">
            {include file="CRM/common/formButtons.tpl" location="bottom"}
        </div>
    </div>
{/crmScope}
