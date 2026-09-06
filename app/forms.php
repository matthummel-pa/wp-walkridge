<?php

/**
 * Contact + newsletter form handlers (admin-post + optional AJAX).
 */

namespace App;

use App\Support\Identity;

add_action('admin_post_nopriv_wr_contact', __NAMESPACE__.'\\wr_handle_contact');
add_action('admin_post_wr_contact', __NAMESPACE__.'\\wr_handle_contact');
add_action('admin_post_nopriv_wr_newsletter', __NAMESPACE__.'\\wr_handle_newsletter');
add_action('admin_post_wr_newsletter', __NAMESPACE__.'\\wr_handle_newsletter');

add_action('wp_ajax_nopriv_wr_contact', __NAMESPACE__.'\\wr_handle_contact_ajax');
add_action('wp_ajax_wr_contact', __NAMESPACE__.'\\wr_handle_contact_ajax');
add_action('wp_ajax_nopriv_wr_newsletter', __NAMESPACE__.'\\wr_handle_newsletter_ajax');
add_action('wp_ajax_wr_newsletter', __NAMESPACE__.'\\wr_handle_newsletter_ajax');

function wr_forms_redirect(string $url, string $status, string $message = ''): void
{
    $args = ['wr_form' => $status];
    if ($message !== '') {
        $args['wr_msg'] = rawurlencode($message);
    }
    wp_safe_redirect(add_query_arg($args, $url));
    exit;
}

function wr_contact_recipient(): string
{
    $email = Identity::email();
    if (is_email($email) && ! str_ends_with($email, '.test')) {
        return $email;
    }

    return (string) get_option('admin_email');
}

/**
 * Persist a contact submission so demos work without a mail transport.
 */
function wr_store_contact_message(string $name, string $email, string $message): void
{
    $messages = get_option('wr_contact_messages', []);
    if (! is_array($messages)) {
        $messages = [];
    }

    $messages[] = [
        'name' => $name,
        'email' => $email,
        'message' => $message,
        'time' => time(),
    ];

    // Cap stored inbox so the options table stays lean.
    if (count($messages) > 100) {
        $messages = array_slice($messages, -100);
    }

    update_option('wr_contact_messages', $messages, false);

    /**
     * Buyers can hook CRM / ticketing here.
     *
     * @param  string  $name  Guest name.
     * @param  string  $email  Guest email.
     * @param  string  $message  Message body.
     */
    do_action('wr_contact_submitted', $name, $email, $message);
}

/**
 * Best-effort mail; returns false when no transport is configured.
 */
function wr_mail_contact(string $name, string $email, string $message): bool
{
    $subject = sprintf(
        /* translators: 1: brand name, 2: sender name */
        __('[%1$s] Message from %2$s', 'walkridge'),
        Identity::brandName(),
        $name
    );
    $body = $message."\n\n---\n".$name.' <'.$email.'>';
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: '.$name.' <'.$email.'>',
    ];

    return (bool) wp_mail(wr_contact_recipient(), $subject, $body, $headers);
}

function wr_handle_contact(): void
{
    $redirect = wp_get_referer() ?: home_url('/contact/');

    if (! isset($_POST['wr_contact_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wr_contact_nonce'])), 'wr_contact')) {
        wr_forms_redirect($redirect, 'error', __('Security check failed. Please try again.', 'walkridge'));
    }

    $name = isset($_POST['cName']) ? sanitize_text_field(wp_unslash($_POST['cName'])) : '';
    $phone = isset($_POST['cPhone']) ? sanitize_text_field(wp_unslash($_POST['cPhone'])) : '';
    $email = isset($_POST['cEmail']) ? sanitize_email(wp_unslash($_POST['cEmail'])) : '';
    $message = isset($_POST['cMsg']) ? sanitize_textarea_field(wp_unslash($_POST['cMsg'])) : '';

    if ($name === '' || $email === '' || ! is_email($email) || $message === '') {
        wr_forms_redirect($redirect, 'error', __('Please add your name, a valid email, and a message.', 'walkridge'));
    }

    if ($phone !== '') {
        /* translators: %s: phone number provided by the visitor */
        $message = sprintf(__('Phone: %s', 'walkridge'), $phone)."\n\n".$message;
    }

    wr_store_contact_message($name, $email, $message);
    wr_mail_contact($name, $email, $message);

    wr_forms_redirect($redirect, 'ok', sprintf(
        /* translators: %s: sender first name-ish */
        __('Thanks, %s — your message is on its way to guest services.', 'walkridge'),
        $name
    ));
}

function wr_handle_newsletter(): void
{
    $redirect = wp_get_referer() ?: home_url('/');

    if (! isset($_POST['wr_newsletter_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wr_newsletter_nonce'])), 'wr_newsletter')) {
        wr_forms_redirect($redirect, 'error', __('Security check failed. Please try again.', 'walkridge'));
    }

    $email = isset($_POST['EMAIL']) ? sanitize_email(wp_unslash($_POST['EMAIL'])) : '';
    if ($email === '' || ! is_email($email)) {
        wr_forms_redirect($redirect, 'error', __('Add a valid email to join the field notes list.', 'walkridge'));
    }

    $list = get_option('wr_newsletter_subscribers', []);
    if (! is_array($list)) {
        $list = [];
    }
    if (! in_array($email, $list, true)) {
        $list[] = $email;
        update_option('wr_newsletter_subscribers', $list, false);
    }

    $subject = sprintf(
        /* translators: %s: brand name */
        __('[%s] New field-notes signup', 'walkridge'),
        Identity::brandName()
    );
    wp_mail(wr_contact_recipient(), $subject, "New signup: {$email}\n", ['Content-Type: text/plain; charset=UTF-8']);

    /**
     * Buyers can hook Mailchimp / FluentCRM / etc. here.
     *
     * @param  string  $email  Subscriber email.
     */
    do_action('wr_newsletter_subscribed', $email);

    wr_forms_redirect($redirect, 'ok', __('You are on the field notes list. Welcome.', 'walkridge'));
}

function wr_handle_contact_ajax(): void
{
    check_ajax_referer('wr_contact', 'nonce');

    $name = isset($_POST['cName']) ? sanitize_text_field(wp_unslash($_POST['cName'])) : '';
    $phone = isset($_POST['cPhone']) ? sanitize_text_field(wp_unslash($_POST['cPhone'])) : '';
    $email = isset($_POST['cEmail']) ? sanitize_email(wp_unslash($_POST['cEmail'])) : '';
    $message = isset($_POST['cMsg']) ? sanitize_textarea_field(wp_unslash($_POST['cMsg'])) : '';

    if ($name === '' || $email === '' || ! is_email($email) || $message === '') {
        wp_send_json_error(['message' => __('Please add your name, a valid email, and a message.', 'walkridge')], 400);
    }

    if ($phone !== '') {
        /* translators: %s: phone number provided by the visitor */
        $message = sprintf(__('Phone: %s', 'walkridge'), $phone)."\n\n".$message;
    }

    wr_store_contact_message($name, $email, $message);
    wr_mail_contact($name, $email, $message);

    wp_send_json_success([
        'message' => sprintf(__('Thanks, %s — your message is on its way to guest services.', 'walkridge'), $name),
    ]);
}

function wr_handle_newsletter_ajax(): void
{
    check_ajax_referer('wr_newsletter', 'nonce');

    $email = isset($_POST['EMAIL']) ? sanitize_email(wp_unslash($_POST['EMAIL'])) : '';
    if ($email === '' || ! is_email($email)) {
        wp_send_json_error(['message' => __('Add a valid email to join the field notes list.', 'walkridge')], 400);
    }

    $list = get_option('wr_newsletter_subscribers', []);
    if (! is_array($list)) {
        $list = [];
    }
    if (! in_array($email, $list, true)) {
        $list[] = $email;
        update_option('wr_newsletter_subscribers', $list, false);
    }

    wp_mail(
        wr_contact_recipient(),
        sprintf(__('[%s] New field-notes signup', 'walkridge'), Identity::brandName()),
        "New signup: {$email}\n"
    );
    do_action('wr_newsletter_subscribed', $email);

    wp_send_json_success(['message' => __('You are on the field notes list. Welcome.', 'walkridge')]);
}

add_action('wp_footer', function () {
    if (is_admin()) {
        return;
    }
    $data = [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'contactNonce' => wp_create_nonce('wr_contact'),
        'newsletterNonce' => wp_create_nonce('wr_newsletter'),
    ];
    echo '<script>window.wrForms='.wp_json_encode($data).';</script>'."\n";
}, 5);
