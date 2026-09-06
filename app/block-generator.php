<?php

/**
 * Custom Block Generator — Tools → Block Generator.
 *
 * Admins create Walkridge custom blocks without writing PHP/JS.
 * Definitions live in wr_custom_blocks. Rendered by walkridge/custom.
 */

namespace App;

add_action('admin_menu', function (): void {
    add_management_page(
        __('Block Generator', 'walkridge'),
        __('Block Generator', 'walkridge'),
        'manage_options',
        'wr-block-generator',
        __NAMESPACE__.'\\wr_block_generator_page'
    );
});

function wr_block_generator_page(): void
{
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'walkridge'));
    }

    $defs = wr_get_custom_block_definitions();
    $saved = false;
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wr_block_gen_nonce'])) {
        if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wr_block_gen_nonce'])), 'wr_block_gen')) {
            wp_die(esc_html__('Security check failed.', 'walkridge'));
        }

        $action = sanitize_key((string) ($_POST['wr_action'] ?? ''));

        if ($action === 'delete' && isset($_POST['wr_delete_id'])) {
            $delId = sanitize_key((string) $_POST['wr_delete_id']);
            unset($defs[$delId]);
            update_option('wr_custom_blocks', $defs, false);
            $saved = true;
        } elseif ($action === 'save') {
            $id = sanitize_key((string) ($_POST['wr_block_id'] ?? ''));
            $title = sanitize_text_field((string) ($_POST['wr_block_title'] ?? ''));
            $desc = sanitize_text_field((string) ($_POST['wr_block_description'] ?? ''));
            $icon = sanitize_key((string) ($_POST['wr_block_icon'] ?? 'star-filled'));

            if ($id === '') {
                $errors[] = __('Block ID is required.', 'walkridge');
            } elseif (! preg_match('/^[a-z][a-z0-9_]*$/', $id)) {
                $errors[] = __('Block ID must start with a letter and contain only lowercase letters, numbers, and underscores.', 'walkridge');
            }

            if ($title === '') {
                $errors[] = __('Block title is required.', 'walkridge');
            }

            if ($errors === []) {
                $fieldNames = array_map('sanitize_key', (array) ($_POST['wr_field_name'] ?? []));
                $fieldLabels = array_map('sanitize_text_field', (array) ($_POST['wr_field_label'] ?? []));
                $fieldTypes = (array) ($_POST['wr_field_type'] ?? []);
                $fieldDefaults = array_map('sanitize_text_field', (array) ($_POST['wr_field_default'] ?? []));

                $fields = [];
                foreach ($fieldNames as $i => $fname) {
                    if ($fname === '') {
                        continue;
                    }
                    $ftype = sanitize_key((string) ($fieldTypes[$i] ?? 'text'));
                    if (! in_array($ftype, ['text', 'textarea', 'url', 'image', 'toggle'], true)) {
                        $ftype = 'text';
                    }
                    $fields[] = [
                        'name' => $fname,
                        'label' => $fieldLabels[$i] ?? $fname,
                        'type' => $ftype,
                        'default' => $fieldDefaults[$i] ?? '',
                    ];
                }

                $defs[$id] = [
                    'id' => $id,
                    'title' => $title,
                    'description' => $desc,
                    'icon' => $icon,
                    'fields' => $fields,
                ];
                update_option('wr_custom_blocks', $defs, false);
                $saved = true;
            }
        }
    }

    $icons = [
        'star-filled' => __('Star', 'walkridge'),
        'admin-page' => __('Page', 'walkridge'),
        'location' => __('Location', 'walkridge'),
        'groups' => __('Team', 'walkridge'),
        'tickets-alt' => __('Tickets', 'walkridge'),
        'flag' => __('Flag', 'walkridge'),
    ];
    ?>
    <div class="wrap">
      <h1><?php esc_html_e('Block Generator', 'walkridge'); ?></h1>
      <p class="description"><?php esc_html_e('Create custom Walkridge blocks without writing code. Generated blocks use walkridge/custom and store field values as block attributes.', 'walkridge'); ?></p>
      <?php if ($saved && $errors === []) { ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Block saved.', 'walkridge'); ?></p></div>
      <?php } ?>
      <?php foreach ($errors as $error) { ?>
        <div class="notice notice-error"><p><?php echo esc_html($error); ?></p></div>
      <?php } ?>

      <form method="post" style="max-width:720px;background:#fff;padding:1.25rem;border:1px solid #c3c4c7;margin:1rem 0;">
        <?php wp_nonce_field('wr_block_gen', 'wr_block_gen_nonce'); ?>
        <input type="hidden" name="wr_action" value="save">
        <table class="form-table" role="presentation">
          <tr>
            <th><label for="wr_block_id"><?php esc_html_e('Block ID', 'walkridge'); ?></label></th>
            <td><input name="wr_block_id" id="wr_block_id" class="regular-text" required pattern="[a-z][a-z0-9_]*" placeholder="testimonial_grid"></td>
          </tr>
          <tr>
            <th><label for="wr_block_title"><?php esc_html_e('Title', 'walkridge'); ?></label></th>
            <td><input name="wr_block_title" id="wr_block_title" class="regular-text" required></td>
          </tr>
          <tr>
            <th><label for="wr_block_description"><?php esc_html_e('Description', 'walkridge'); ?></label></th>
            <td><input name="wr_block_description" id="wr_block_description" class="large-text"></td>
          </tr>
          <tr>
            <th><label for="wr_block_icon"><?php esc_html_e('Icon', 'walkridge'); ?></label></th>
            <td>
              <select name="wr_block_icon" id="wr_block_icon">
                <?php foreach ($icons as $slug => $label) { ?>
                  <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($label); ?></option>
                <?php } ?>
              </select>
            </td>
          </tr>
        </table>
        <h2><?php esc_html_e('Fields', 'walkridge'); ?></h2>
        <div id="wr-fields">
          <div class="wr-field-row" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:0.5rem;margin-bottom:0.5rem;">
            <input name="wr_field_name[]" placeholder="<?php esc_attr_e('field_id', 'walkridge'); ?>">
            <input name="wr_field_label[]" placeholder="<?php esc_attr_e('Label', 'walkridge'); ?>">
            <select name="wr_field_type[]">
              <option value="text"><?php esc_html_e('Text', 'walkridge'); ?></option>
              <option value="textarea"><?php esc_html_e('Textarea', 'walkridge'); ?></option>
              <option value="url"><?php esc_html_e('URL', 'walkridge'); ?></option>
              <option value="image"><?php esc_html_e('Image URL', 'walkridge'); ?></option>
              <option value="toggle"><?php esc_html_e('Toggle', 'walkridge'); ?></option>
            </select>
            <input name="wr_field_default[]" placeholder="<?php esc_attr_e('Default', 'walkridge'); ?>">
            <span></span>
          </div>
        </div>
        <p><button type="button" class="button" id="wr-add-field">+ <?php esc_html_e('Add field', 'walkridge'); ?></button></p>
        <p><button type="submit" class="button button-primary"><?php esc_html_e('Save block', 'walkridge'); ?></button></p>
      </form>

      <h2><?php esc_html_e('Saved custom blocks', 'walkridge'); ?></h2>
      <?php if ($defs === []) { ?>
        <p><?php esc_html_e('None yet.', 'walkridge'); ?></p>
      <?php } else { ?>
        <ul>
          <?php foreach ($defs as $def) { ?>
            <li style="margin-bottom:0.75rem;">
              <strong><?php echo esc_html((string) ($def['title'] ?? '')); ?></strong>
              <code>walkridge/custom · <?php echo esc_html((string) ($def['id'] ?? '')); ?></code>
              <form method="post" style="display:inline">
                <?php wp_nonce_field('wr_block_gen', 'wr_block_gen_nonce'); ?>
                <input type="hidden" name="wr_action" value="delete">
                <input type="hidden" name="wr_delete_id" value="<?php echo esc_attr((string) ($def['id'] ?? '')); ?>">
                <button class="button-link-delete" onclick="return confirm('Delete this block?')"><?php esc_html_e('Delete', 'walkridge'); ?></button>
              </form>
            </li>
          <?php } ?>
        </ul>
      <?php } ?>
    </div>
    <script>
    document.getElementById('wr-add-field')?.addEventListener('click', function () {
      var row = document.querySelector('#wr-fields .wr-field-row').cloneNode(true);
      row.querySelectorAll('input').forEach(function (i) { i.value = ''; });
      document.getElementById('wr-fields').appendChild(row);
    });
    </script>
    <?php
}
