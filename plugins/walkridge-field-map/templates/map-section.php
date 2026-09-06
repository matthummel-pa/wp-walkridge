<?php
/**
 * Field map shortcode output — rendered by [wr_field_map].
 *
 * Variables in scope (set by WRFM_Plugin::render_shortcode):
 *   $atts['height']  CSS height for the map stage (default: 620px)
 *   $atts['class']   Extra CSS classes on the wrapper
 */
defined('ABSPATH') || exit;
$height = esc_attr($atts['height']);
$extra_cls = esc_attr($atts['class']);
?>
<div class="hgfm-wrapper <?php echo $extra_cls; ?>">

  <section class="section" id="the-ground">
    <div class="wrap">
      <div class="prose reveal">
        <span class="eyebrow"><?php esc_html_e('The Battlefield', 'wr-field-map'); ?></span>
        <h2 style="margin-block:.4rem 1rem;font-size:clamp(1.8rem,4vw,2.5rem);"><?php esc_html_e('Where the Battle of Gettysburg was fought', 'wr-field-map'); ?></h2>
        <p><?php esc_html_e('The Battle of Gettysburg was fought July 1–3, 1863, across the ridges, fields, and hills surrounding the borough of Gettysburg, Pennsylvania. Today that ground is preserved as', 'wr-field-map'); ?>
          <strong><?php esc_html_e('Gettysburg National Military Park', 'wr-field-map'); ?></strong>, <?php esc_html_e('with more than 1,300 monuments across roughly 6,000 acres. Our tours make sense of that landscape — not just the maps, but the actual rises and swales where the fighting turned.', 'wr-field-map'); ?></p>

        <h3><?php esc_html_e('Seminary & Cemetery Ridge', 'wr-field-map'); ?></h3>
        <p><?php esc_html_e('Two long parallel ridges shaped the battle.', 'wr-field-map'); ?>
           <strong data-map-place="seminary-ridge"><?php esc_html_e('Seminary Ridge', 'wr-field-map'); ?></strong>,
           <?php esc_html_e('marked by the cupola of the old Lutheran seminary, became the Confederate line after the first day. Facing it,', 'wr-field-map'); ?>
           <strong data-map-place="cemetery-ridge"><?php esc_html_e('Cemetery Ridge', 'wr-field-map'); ?></strong>
           <?php esc_html_e('anchored the Union "fishhook" and became the target of Pickett\'s Charge on July 3. Our Battlefield Highlights Walking Tour crosses Cemetery Ridge and', 'wr-field-map'); ?>
           <span data-map-place="mcpherson-ridge"><?php esc_html_e('McPherson Ridge', 'wr-field-map'); ?></span>
           <?php esc_html_e('to connect all three days into a single, walkable story.', 'wr-field-map'); ?></p>

        <h3><?php esc_html_e('Little Round Top, Big Round Top & Devil\'s Den', 'wr-field-map'); ?></h3>
        <p><strong data-map-place="little-round-top"><?php esc_html_e('Little Round Top', 'wr-field-map'); ?></strong>
           <?php esc_html_e('and', 'wr-field-map'); ?>
           <strong data-map-place="big-round-top"><?php esc_html_e('Big Round Top', 'wr-field-map'); ?></strong>
           <?php esc_html_e('command the high ground Union forces fought to hold on July 2. Just below them, the jumbled boulders of', 'wr-field-map'); ?>
           <strong data-map-place="devils-den"><?php esc_html_e("Devil's Den", 'wr-field-map'); ?></strong>
           <?php esc_html_e('saw brutal close fighting. Our hike climbs this ground.', 'wr-field-map'); ?></p>

        <h3><?php esc_html_e('Downtown, the Cemetery & the David Wills House', 'wr-field-map'); ?></h3>
        <p><strong data-map-place="lincoln-square"><?php esc_html_e('Lincoln Square', 'wr-field-map'); ?></strong>
           <?php esc_html_e('is the civic heart of downtown Gettysburg. The', 'wr-field-map'); ?>
           <strong data-map-place="david-wills-house"><?php esc_html_e('David Wills House', 'wr-field-map'); ?></strong>
           <?php esc_html_e('on the square is where Abraham Lincoln finished the Gettysburg Address the night before delivering it. Our evening Ghosts of Gettysburg Lantern Walk winds through these downtown streets after dark.', 'wr-field-map'); ?></p>

        <h3><?php esc_html_e('Where to meet us & parking', 'wr-field-map'); ?></h3>
        <p><?php esc_html_e('This concept uses a sample ticket office at', 'wr-field-map'); ?>
           <strong data-map-place="sample-office"><?php esc_html_e('100 Sample Street, Gettysburg, PA 17325', 'wr-field-map'); ?></strong>
           — <?php esc_html_e('not a live storefront. Use downtown public lots and metered street parking near Lincoln Square. Evening lantern walks use a sample downtown meet at the', 'wr-field-map'); ?>
           <strong data-map-place="lincoln-square"><?php esc_html_e('Lincoln Square flagpole', 'wr-field-map'); ?></strong>.</p>
      </div>
    </div>
  </section>

  {{-- Interactive field map --}}
  <section class="section section-alt" id="field-list">
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow"><?php esc_html_e('Your field list', 'wr-field-map'); ?></span>
        <h2><?php esc_html_e('Build this walk.', 'wr-field-map'); ?></h2>
        <p><?php esc_html_e('Add the ground you want to walk. Click a pin to open it on the map. When the list has stops, your completed walk draws below.', 'wr-field-map'); ?></p>
      </div>

      <div class="diorama-layout diorama-layout--stack reveal">
        <div class="map-under-card">
          <p class="dio-list-kicker"><?php esc_html_e('Tour geography', 'wr-field-map'); ?></p>
          <h3><?php esc_html_e('Names we use on the field.', 'wr-field-map'); ?></h3>
          <p class="lede"><?php esc_html_e('Pin to open the overlay. Add to put it on your list.', 'wr-field-map'); ?></p>
          <label class="dio-search-label" for="monument-search"><?php esc_html_e('Find a monument', 'wr-field-map'); ?></label>
          <input id="monument-search" class="dio-search" type="search"
                 data-monument-search
                 placeholder="<?php esc_attr_e('Pennsylvania Memorial, 20th Maine…', 'wr-field-map'); ?>"
                 autocomplete="off">
          <div class="dio-place-list" data-monument-hits></div>
          <ul class="dio-place-list" data-diorama-list></ul>
        </div>

        <div class="map-under-card itinerary-card" data-itinerary>
          <span class="eyebrow"><?php esc_html_e('Your field list', 'wr-field-map'); ?></span>
          <h3><?php esc_html_e('Itinerary', 'wr-field-map'); ?> <span data-itinerary-count>0</span></h3>
          <p class="lede" data-itinerary-empty><?php esc_html_e('No stops yet. Add from the list, or from a map popup.', 'wr-field-map'); ?></p>
          <ol class="itin-list" data-itinerary-list></ol>
          <div class="itin-actions">
            <button type="button" class="btn btn-primary btn-sm" data-itinerary-pdf>
              <?php esc_html_e('Save as PDF', 'wr-field-map'); ?>
            </button>
            <button type="button" class="btn btn-ghost btn-sm" data-itinerary-print>
              <?php esc_html_e('Print', 'wr-field-map'); ?>
            </button>
          </div>
          <p data-itinerary-status role="status" aria-live="polite"></p>
        </div>
      </div>
    </div>
  </section>

  <section class="section" id="your-walk-map" data-itinerary-map-section hidden>
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow"><?php esc_html_e('Completed walk', 'wr-field-map'); ?></span>
        <h2><?php esc_html_e('Your walk, mapped.', 'wr-field-map'); ?></h2>
        <p><?php esc_html_e('Every stop on this itinerary, numbered in the order you added it.', 'wr-field-map'); ?></p>
      </div>
      <div class="diorama-stage" data-itinerary-map
           aria-label="<?php esc_attr_e('Map of your field itinerary', 'wr-field-map'); ?>"></div>
    </div>
  </section>

</div>

{{-- Full-screen map overlay (opens when pins are clicked from prose text) --}}
<div class="modal-overlay map-field-overlay" data-map-overlay hidden>
  <div class="modal-panel map-overlay-panel" role="dialog" aria-modal="true"
       aria-labelledby="mapOverlayTitle">
    <div class="modal-head">
      <h2 id="mapOverlayTitle"><?php esc_html_e('Gettysburg field map', 'wr-field-map'); ?></h2>
      <button type="button" class="modal-close" data-map-overlay-close
              aria-label="<?php esc_attr_e('Close map', 'wr-field-map'); ?>">&times;</button>
    </div>
    <div class="map-overlay-body">
      <div class="diorama-stage" data-diorama data-diorama-mode="view"
           style="min-height:<?php echo $height; ?>;"
           aria-label="<?php esc_attr_e('Interactive OpenLayers map of Gettysburg', 'wr-field-map'); ?>">
      </div>
    </div>
  </div>
</div>
