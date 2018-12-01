<script id="tmpl-nf-stripe-meta-repeater-row" type="text/template">
    <div>
        <span class="dashicons dashicons-menu handle"></span>
    </div>
    <div>
        <input type="text" class="setting" value="{{{ data.key }}}" data-id="key">
        <span class="nf-option-error"></span>
    </div>
    <div>
        <label class="has-merge-tags">
            <input type="text" class="setting" value="{{{ data.value }}}" data-id="value">
            <span class="dashicons dashicons-list-view merge-tags"></span>
            <span class="nf-option-error"></span>
        </label>
    </div>
    <div>
        <span class="dashicons dashicons-dismiss nf-delete"></span>
    </div>
</script>