/**
 * Walkridge Gutenberg blocks — editor registration.
 *
 * All blocks are server-side rendered (save returns null).
 * The inspector sidebar drives attribute edits; ServerSideRender
 * shows a live PHP-rendered preview in the editor canvas.
 *
 * Advanced controls per block type:
 *  – URLInput for all URL fields (link picker + validation)
 *  – MediaUpload / MediaUploadCheck for custom image selection
 *  – BlockControls toolbar for heading-level + text-alignment
 *  – RangeControl, ToggleControl, SelectControl for display options
 */

import { registerBlockType, getCategories, setCategories } from '@wordpress/blocks';
import {
  InspectorControls,
  BlockControls,
  MediaUpload,
  MediaUploadCheck,
  URLInput,
  HeadingLevelDropdown,
  AlignmentControl,
  useBlockProps,
} from '@wordpress/block-editor';
import {
  PanelBody,
  TextControl,
  TextareaControl,
  ToggleControl,
  RangeControl,
  SelectControl,
  Spinner,
  Button,
  ToolbarGroup,
  Notice,
} from '@wordpress/components';
import { createElement as el, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

// ── Register the Walkridge block category ─────────────────────────
if (!getCategories().find((c) => c.slug === 'walkridge')) {
  setCategories([
    ...getCategories(),
    { slug: 'walkridge', title: 'Walkridge', icon: null },
  ]);
}

const cfg = window.WALKRIDGE_BLOCKS || {};

// ── Shared SSR wrapper ────────────────────────────────────────────
function SsrEdit({ name, attributes, sidebar, toolbar }) {
  const blockProps = useBlockProps({ className: 'wr-block-ssr' });
  return el(
    Fragment,
    null,
    toolbar ? el(BlockControls, null, toolbar()) : null,
    el(InspectorControls, null, sidebar()),
    el(
      'div',
      blockProps,
      el(ServerSideRender, {
        block: name,
        attributes,
        LoadingResponsePlaceholder: () =>
          el(
            'div',
            { style: { padding: 32, textAlign: 'center', opacity: 0.5 } },
            el(Spinner),
            el('p', { style: { marginTop: 8, fontSize: 12 } }, __('Rendering preview…', 'walkridge')),
          ),
        ErrorResponsePlaceholder: ({ response }) =>
          el(
            Notice,
            { status: 'warning', isDismissible: false, style: { margin: 8 } },
            __('Preview error — check block attributes or save the page to refresh.', 'walkridge'),
          ),
      }),
    ),
  );
}

// ── Reusable helpers ──────────────────────────────────────────────

/** Plain text / textarea panel. */
function textPanel(title, fields, attrs, set, initialOpen = true) {
  return el(
    PanelBody,
    { title, initialOpen },
    ...fields.map(([key, label, multiline]) =>
      el(multiline ? TextareaControl : TextControl, {
        key,
        label,
        value: attrs[key] || '',
        onChange: (v) => set({ [key]: v }),
      }),
    ),
  );
}

/** URL picker using URLInput (shows site search + typing). */
function urlField(label, key, attrs, set) {
  return el(
    'div',
    { key, style: { marginBottom: 16 } },
    el(
      'label',
      {
        style: {
          display: 'block',
          marginBottom: 4,
          fontSize: 11,
          fontWeight: 500,
          textTransform: 'uppercase',
          color: 'var(--wp-components-color-accent,#007cba)',
        },
      },
      label,
    ),
    el(URLInput, {
      value: attrs[key] || '',
      onChange: (v) => set({ [key]: v }),
      isFullWidth: true,
      hasBorder: true,
    }),
  );
}

/** Media upload button that sets a URL attribute. */
function mediaUploadField(label, urlKey, attrs, set) {
  const hasImage = !!(attrs[urlKey] || '');
  return el(
    PanelBody,
    { title: label, initialOpen: false },
    el(
      MediaUploadCheck,
      null,
      el(MediaUpload, {
        onSelect: (media) => set({ [urlKey]: media.url }),
        allowedTypes: ['image'],
        render: ({ open }) =>
          el(
            Fragment,
            null,
            hasImage &&
              el('img', {
                src: attrs[urlKey],
                alt: '',
                style: {
                  width: '100%',
                  height: 120,
                  objectFit: 'cover',
                  borderRadius: 2,
                  marginBottom: 8,
                },
              }),
            el(
              Button,
              {
                onClick: open,
                variant: hasImage ? 'secondary' : 'primary',
                style: { width: '100%', justifyContent: 'center' },
              },
              hasImage ? __('Replace image', 'walkridge') : __('Choose image', 'walkridge'),
            ),
            hasImage &&
              el(
                Button,
                {
                  onClick: () => set({ [urlKey]: '' }),
                  variant: 'tertiary',
                  isDestructive: true,
                  style: { marginTop: 4, width: '100%', justifyContent: 'center' },
                },
                __('Remove image', 'walkridge'),
              ),
          ),
      }),
    ),
  );
}

// Heading levels available to the section-heading block
const HEADING_LEVELS = [2, 3, 4, 5];

// ─────────────────────────────────────────────────────────────────
// Block: walkridge/home-hero
// ─────────────────────────────────────────────────────────────────
registerBlockType('walkridge/home-hero', {
  title: __('Home Hero', 'walkridge'),
  category: 'walkridge',
  icon: 'cover-image',
  supports: { html: false, multiple: false },
  attributes: {
    eyebrow: { type: 'string', default: '' },
    title: { type: 'string', default: 'Walk the ground where <em>history turned.</em>' },
    text: { type: 'string', default: '' },
    primaryLabel: { type: 'string', default: '' },
    primaryUrl: { type: 'string', default: '' },
    secondaryLabel: { type: 'string', default: 'See All Tours' },
    secondaryUrl: { type: 'string', default: '' },
    imageKey: { type: 'string', default: 'cannon' },
    imageUrl: { type: 'string', default: '' },
  },
  edit({ attributes: a, setAttributes: s }) {
    return el(SsrEdit, {
      name: 'walkridge/home-hero',
      attributes: a,
      sidebar: () =>
        el(
          Fragment,
          null,
          textPanel(
            __('Hero copy', 'walkridge'),
            [
              ['eyebrow', __('Eyebrow', 'walkridge'), false],
              ['title', __('Title (HTML: em/strong)', 'walkridge'), false],
              ['text', __('Lede paragraph', 'walkridge'), true],
              ['primaryLabel', __('Primary button label', 'walkridge'), false],
              ['secondaryLabel', __('Secondary button label', 'walkridge'), false],
            ],
            a,
            s,
          ),
          el(
            PanelBody,
            { title: __('Button URLs', 'walkridge'), initialOpen: false },
            urlField(__('Primary button URL', 'walkridge'), 'primaryUrl', a, s),
            urlField(__('Secondary button URL', 'walkridge'), 'secondaryUrl', a, s),
          ),
          el(
            PanelBody,
            { title: __('Background image', 'walkridge'), initialOpen: false },
            el(SelectControl, {
              label: __('Preset image', 'walkridge'),
              value: a.imageKey || 'cannon',
              options: [
                { label: __('Cannon (default)', 'walkridge'), value: 'cannon' },
                { label: __('Wentz farmstead', 'walkridge'), value: 'wentz' },
                { label: __('Downtown Gettysburg', 'walkridge'), value: 'downtown' },
              ],
              onChange: (v) => s({ imageKey: v }),
              help: __('Overridden by a custom image upload below.', 'walkridge'),
            }),
          ),
          mediaUploadField(__('Custom background image', 'walkridge'), 'imageUrl', a, s),
        ),
    });
  },
  save: () => null,
});

// ─────────────────────────────────────────────────────────────────
// Block: walkridge/page-intro
// ─────────────────────────────────────────────────────────────────
registerBlockType('walkridge/page-intro', {
  title: __('Page Intro', 'walkridge'),
  category: 'walkridge',
  icon: 'heading',
  supports: { html: false },
  attributes: {
    eyebrow: { type: 'string', default: '' },
    heading: { type: 'string', default: '' },
    intro: { type: 'string', default: '' },
  },
  edit({ attributes: a, setAttributes: s }) {
    return el(SsrEdit, {
      name: 'walkridge/page-intro',
      attributes: a,
      sidebar: () =>
        el(
          Fragment,
          null,
          el(
            Notice,
            {
              status: 'info',
              isDismissible: false,
              style: { margin: '8px 16px' },
            },
            __(
              'Defaults are pulled from page-slug presets when fields are left empty.',
              'walkridge',
            ),
          ),
          textPanel(
            __('Page intro', 'walkridge'),
            [
              ['eyebrow', __('Eyebrow label', 'walkridge'), false],
              ['heading', __('Heading', 'walkridge'), false],
              ['intro', __('Intro paragraph', 'walkridge'), true],
            ],
            a,
            s,
          ),
        ),
    });
  },
  save: () => null,
});

// ─────────────────────────────────────────────────────────────────
// Block: walkridge/info-strip
// ─────────────────────────────────────────────────────────────────
registerBlockType('walkridge/info-strip', {
  title: __('Info Strip', 'walkridge'),
  category: 'walkridge',
  icon: 'info',
  supports: { html: false },
  attributes: {
    showPhone: { type: 'boolean', default: true },
    showAddress: { type: 'boolean', default: true },
    showHours: { type: 'boolean', default: true },
  },
  edit({ attributes: a, setAttributes: s }) {
    return el(SsrEdit, {
      name: 'walkridge/info-strip',
      attributes: a,
      sidebar: () =>
        el(
          PanelBody,
          { title: __('Visible items', 'walkridge'), initialOpen: true },
          el(ToggleControl, {
            label: __('Phone number', 'walkridge'),
            checked: !!a.showPhone,
            onChange: (v) => s({ showPhone: v }),
          }),
          el(ToggleControl, {
            label: __('Address', 'walkridge'),
            checked: !!a.showAddress,
            onChange: (v) => s({ showAddress: v }),
          }),
          el(ToggleControl, {
            label: __('Hours', 'walkridge'),
            checked: !!a.showHours,
            onChange: (v) => s({ showHours: v }),
          }),
        ),
    });
  },
  save: () => null,
});

// ─────────────────────────────────────────────────────────────────
// Block: walkridge/section-heading
// Advanced: heading level toolbar + text alignment toolbar
// ─────────────────────────────────────────────────────────────────
registerBlockType('walkridge/section-heading', {
  title: __('Section Heading', 'walkridge'),
  category: 'walkridge',
  icon: 'editor-textcolor',
  supports: { html: false },
  attributes: {
    eyebrow: { type: 'string', default: '' },
    heading: { type: 'string', default: '' },
    text: { type: 'string', default: '' },
    anchor: { type: 'string', default: '' },
    alt: { type: 'boolean', default: false },
    headingLevel: { type: 'number', default: 2 },
    textAlign: { type: 'string', default: 'center' },
  },
  edit({ attributes: a, setAttributes: s }) {
    return el(SsrEdit, {
      name: 'walkridge/section-heading',
      attributes: a,
      toolbar: () =>
        el(
          Fragment,
          null,
          // WP core heading-level picker — matches the core/heading block UX
          el(
            ToolbarGroup,
            null,
            el(HeadingLevelDropdown, {
              value: a.headingLevel || 2,
              options: HEADING_LEVELS,
              onChange: (v) => s({ headingLevel: v }),
            }),
          ),
          // WP core text-alignment control
          el(
            ToolbarGroup,
            null,
            el(AlignmentControl, {
              value: a.textAlign || 'center',
              onChange: (v) => s({ textAlign: v || 'center' }),
              alignmentControls: [
                { align: 'left', title: __('Align left', 'walkridge') },
                { align: 'center', title: __('Align center', 'walkridge') },
                { align: 'right', title: __('Align right', 'walkridge') },
              ],
            }),
          ),
        ),
      sidebar: () =>
        el(
          Fragment,
          null,
          textPanel(
            __('Content', 'walkridge'),
            [
              ['eyebrow', __('Eyebrow label', 'walkridge'), false],
              ['heading', __('Heading (HTML: em/strong)', 'walkridge'), false],
              ['text', __('Supporting text', 'walkridge'), true],
              ['anchor', __('Anchor ID (for #links)', 'walkridge'), false],
            ],
            a,
            s,
          ),
          el(
            PanelBody,
            { title: __('Style', 'walkridge'), initialOpen: false },
            el(ToggleControl, {
              label: __('Alternate background (dark surface)', 'walkridge'),
              checked: !!a.alt,
              onChange: (v) => s({ alt: v }),
            }),
          ),
        ),
    });
  },
  save: () => null,
});

// ─────────────────────────────────────────────────────────────────
// Block: walkridge/tour-grid
// ─────────────────────────────────────────────────────────────────
registerBlockType('walkridge/tour-grid', {
  title: __('Tour Grid', 'walkridge'),
  category: 'walkridge',
  icon: 'tickets-alt',
  supports: { html: false },
  attributes: {
    limit: { type: 'number', default: 0 },
    showFilters: { type: 'boolean', default: true },
    showCompare: { type: 'boolean', default: true },
    eyebrow: { type: 'string', default: 'Choose Your Tour' },
    heading: { type: 'string', default: 'Ways to walk the field.' },
    text: { type: 'string', default: '' },
  },
  edit({ attributes: a, setAttributes: s }) {
    return el(SsrEdit, {
      name: 'walkridge/tour-grid',
      attributes: a,
      sidebar: () =>
        el(
          Fragment,
          null,
          textPanel(
            __('Section copy', 'walkridge'),
            [
              ['eyebrow', __('Eyebrow label', 'walkridge'), false],
              ['heading', __('Heading', 'walkridge'), false],
              ['text', __('Supporting text', 'walkridge'), true],
            ],
            a,
            s,
          ),
          el(
            PanelBody,
            { title: __('Display options', 'walkridge'), initialOpen: true },
            el(RangeControl, {
              label: __('Max tours shown (0 = all)', 'walkridge'),
              value: a.limit || 0,
              min: 0,
              max: 12,
              step: 1,
              marks: true,
              onChange: (v) => s({ limit: v }),
            }),
            el(ToggleControl, {
              label: __('Show category filter bar', 'walkridge'),
              checked: !!a.showFilters,
              onChange: (v) => s({ showFilters: v }),
            }),
            el(ToggleControl, {
              label: __('Show comparison table', 'walkridge'),
              checked: !!a.showCompare,
              onChange: (v) => s({ showCompare: v }),
            }),
          ),
        ),
    });
  },
  save: () => null,
});

// ─────────────────────────────────────────────────────────────────
// Block: walkridge/pathway-cards
// Advanced: URLInput for both card links
// ─────────────────────────────────────────────────────────────────
registerBlockType('walkridge/pathway-cards', {
  title: __('Pathway Cards', 'walkridge'),
  category: 'walkridge',
  icon: 'table-col-after',
  supports: { html: false },
  attributes: {
    eyebrow: { type: 'string', default: 'Two Ways In' },
    heading: { type: 'string', default: 'The field by day. The town after dark.' },
    text: { type: 'string', default: '' },
    leftEyebrow: { type: 'string', default: 'Historical' },
    leftTitle: { type: 'string', default: '' },
    leftText: { type: 'string', default: '' },
    leftUrl: { type: 'string', default: '' },
    rightEyebrow: { type: 'string', default: 'After Dark' },
    rightTitle: { type: 'string', default: '' },
    rightText: { type: 'string', default: '' },
    rightUrl: { type: 'string', default: '' },
  },
  edit({ attributes: a, setAttributes: s }) {
    return el(SsrEdit, {
      name: 'walkridge/pathway-cards',
      attributes: a,
      sidebar: () =>
        el(
          Fragment,
          null,
          textPanel(
            __('Section intro', 'walkridge'),
            [
              ['eyebrow', __('Eyebrow label', 'walkridge'), false],
              ['heading', __('Heading', 'walkridge'), false],
              ['text', __('Supporting text', 'walkridge'), true],
            ],
            a,
            s,
          ),
          el(
            PanelBody,
            { title: __('Left card — Historical', 'walkridge'), initialOpen: true },
            el(TextControl, {
              label: __('Eyebrow', 'walkridge'),
              value: a.leftEyebrow || '',
              onChange: (v) => s({ leftEyebrow: v }),
            }),
            el(TextControl, {
              label: __('Title', 'walkridge'),
              value: a.leftTitle || '',
              onChange: (v) => s({ leftTitle: v }),
            }),
            el(TextareaControl, {
              label: __('Text', 'walkridge'),
              value: a.leftText || '',
              onChange: (v) => s({ leftText: v }),
            }),
            urlField(__('Card link URL', 'walkridge'), 'leftUrl', a, s),
          ),
          el(
            PanelBody,
            { title: __('Right card — After Dark', 'walkridge'), initialOpen: true },
            el(TextControl, {
              label: __('Eyebrow', 'walkridge'),
              value: a.rightEyebrow || '',
              onChange: (v) => s({ rightEyebrow: v }),
            }),
            el(TextControl, {
              label: __('Title', 'walkridge'),
              value: a.rightTitle || '',
              onChange: (v) => s({ rightTitle: v }),
            }),
            el(TextareaControl, {
              label: __('Text', 'walkridge'),
              value: a.rightText || '',
              onChange: (v) => s({ rightText: v }),
            }),
            urlField(__('Card link URL', 'walkridge'), 'rightUrl', a, s),
          ),
        ),
    });
  },
  save: () => null,
});

// ─────────────────────────────────────────────────────────────────
// Block: walkridge/about-split
// Advanced: MediaUpload for custom image, URLInput for CTAs,
//           flip-layout toggle
// ─────────────────────────────────────────────────────────────────
registerBlockType('walkridge/about-split', {
  title: __('About Split', 'walkridge'),
  category: 'walkridge',
  icon: 'align-pull-left',
  supports: { html: false },
  attributes: {
    eyebrow: { type: 'string', default: 'About Us' },
    heading: { type: 'string', default: 'Guided by licensed historians, not a script.' },
    text: { type: 'string', default: '' },
    primaryLabel: { type: 'string', default: 'Meet Your Guides' },
    primaryUrl: { type: 'string', default: '' },
    secondaryLabel: { type: 'string', default: 'About the Area' },
    secondaryUrl: { type: 'string', default: '' },
    imageKey: { type: 'string', default: 'wentz' },
    imageUrl: { type: 'string', default: '' },
    caption: { type: 'string', default: '' },
    flip: { type: 'boolean', default: false },
  },
  edit({ attributes: a, setAttributes: s }) {
    return el(SsrEdit, {
      name: 'walkridge/about-split',
      attributes: a,
      sidebar: () =>
        el(
          Fragment,
          null,
          textPanel(
            __('Copy', 'walkridge'),
            [
              ['eyebrow', __('Eyebrow label', 'walkridge'), false],
              ['heading', __('Heading', 'walkridge'), false],
              ['text', __('Body (HTML allowed)', 'walkridge'), true],
            ],
            a,
            s,
          ),
          el(
            PanelBody,
            { title: __('Call-to-action buttons', 'walkridge'), initialOpen: false },
            el(TextControl, {
              label: __('Primary button label', 'walkridge'),
              value: a.primaryLabel || '',
              onChange: (v) => s({ primaryLabel: v }),
            }),
            urlField(__('Primary button URL', 'walkridge'), 'primaryUrl', a, s),
            el(TextControl, {
              label: __('Secondary button label', 'walkridge'),
              value: a.secondaryLabel || '',
              onChange: (v) => s({ secondaryLabel: v }),
            }),
            urlField(__('Secondary button URL', 'walkridge'), 'secondaryUrl', a, s),
          ),
          el(
            PanelBody,
            { title: __('Layout', 'walkridge'), initialOpen: false },
            el(ToggleControl, {
              label: __('Flip: image on the left', 'walkridge'),
              checked: !!a.flip,
              onChange: (v) => s({ flip: v }),
              help: __('By default the image is on the right.', 'walkridge'),
            }),
          ),
          el(
            PanelBody,
            { title: __('Image', 'walkridge'), initialOpen: false },
            el(SelectControl, {
              label: __('Preset image', 'walkridge'),
              value: a.imageKey || 'wentz',
              options: [
                { label: __('Wentz farmstead (default)', 'walkridge'), value: 'wentz' },
                { label: __('Cannon', 'walkridge'), value: 'cannon' },
                { label: __('Downtown Gettysburg', 'walkridge'), value: 'downtown' },
              ],
              onChange: (v) => s({ imageKey: v }),
              help: __('Overridden by a custom upload below.', 'walkridge'),
            }),
            el(TextControl, {
              label: __('Caption', 'walkridge'),
              value: a.caption || '',
              onChange: (v) => s({ caption: v }),
            }),
          ),
          mediaUploadField(__('Custom image upload', 'walkridge'), 'imageUrl', a, s),
        ),
    });
  },
  save: () => null,
});

// ─────────────────────────────────────────────────────────────────
// Block: walkridge/book-band
// ─────────────────────────────────────────────────────────────────
registerBlockType('walkridge/book-band', {
  title: __('Book Band', 'walkridge'),
  category: 'walkridge',
  icon: 'cart',
  supports: { html: false },
  attributes: {
    heading: { type: 'string', default: '' },
    text: { type: 'string', default: '' },
    buttonLabel: { type: 'string', default: '' },
  },
  edit({ attributes: a, setAttributes: s }) {
    return el(SsrEdit, {
      name: 'walkridge/book-band',
      attributes: a,
      sidebar: () =>
        el(
          Fragment,
          null,
          el(
            Notice,
            { status: 'info', isDismissible: false, style: { margin: '8px 16px' } },
            __('This is the page-level booking CTA — distinct from the global site footer.', 'walkridge'),
          ),
          textPanel(
            __('Booking CTA', 'walkridge'),
            [
              ['heading', __('Heading', 'walkridge'), false],
              ['text', __('Supporting text', 'walkridge'), true],
              ['buttonLabel', __('Button label', 'walkridge'), false],
            ],
            a,
            s,
          ),
        ),
    });
  },
  save: () => null,
});

// ─────────────────────────────────────────────────────────────────
// Block: walkridge/cta-band
// Advanced: URLInput for button URL
// ─────────────────────────────────────────────────────────────────
registerBlockType('walkridge/cta-band', {
  title: __('CTA Band', 'walkridge'),
  category: 'walkridge',
  icon: 'megaphone',
  supports: { html: false },
  attributes: {
    eyebrow: { type: 'string', default: '' },
    heading: { type: 'string', default: '' },
    text: { type: 'string', default: '' },
    buttonLabel: { type: 'string', default: '' },
    buttonUrl: { type: 'string', default: '' },
  },
  edit({ attributes: a, setAttributes: s }) {
    return el(SsrEdit, {
      name: 'walkridge/cta-band',
      attributes: a,
      sidebar: () =>
        el(
          Fragment,
          null,
          textPanel(
            __('CTA copy', 'walkridge'),
            [
              ['eyebrow', __('Eyebrow label', 'walkridge'), false],
              ['heading', __('Heading', 'walkridge'), false],
              ['text', __('Supporting text', 'walkridge'), true],
              ['buttonLabel', __('Button label', 'walkridge'), false],
            ],
            a,
            s,
          ),
          el(
            PanelBody,
            { title: __('Button URL', 'walkridge'), initialOpen: false },
            urlField(__('Destination URL', 'walkridge'), 'buttonUrl', a, s),
          ),
        ),
    });
  },
  save: () => null,
});

// ─────────────────────────────────────────────────────────────────
// Block: walkridge/custom (generator)
// ─────────────────────────────────────────────────────────────────
registerBlockType('walkridge/custom', {
  title: __('Custom (Generator)', 'walkridge'),
  category: 'walkridge',
  icon: 'admin-generic',
  supports: { html: false },
  attributes: {
    blockId: { type: 'string', default: '' },
    values: { type: 'object', default: {} },
  },
  edit({ attributes: a, setAttributes: s }) {
    const defs = cfg.customBlocks || {};
    const options = [
      { label: __('Select a generated block…', 'walkridge'), value: '' },
      ...Object.keys(defs).map((id) => ({
        label: defs[id].title || id,
        value: id,
      })),
    ];
    const def = defs[a.blockId] || null;
    const values = a.values || {};

    return el(SsrEdit, {
      name: 'walkridge/custom',
      attributes: a,
      sidebar: () =>
        el(
          Fragment,
          null,
          el(
            PanelBody,
            { title: __('Custom block', 'walkridge'), initialOpen: true },
            el(SelectControl, {
              label: __('Definition', 'walkridge'),
              value: a.blockId || '',
              options,
              onChange: (v) => s({ blockId: v, values: {} }),
            }),
            !options.slice(1).length &&
              el(
                'p',
                { className: 'description' },
                __('Create definitions under Tools → Walkridge Blocks.', 'walkridge'),
              ),
          ),
          def &&
            el(
              PanelBody,
              { title: __('Fields', 'walkridge'), initialOpen: true },
              ...(def.fields || []).map((field) => {
                const name = field.name;
                const label = field.label || name;
                const type = field.type || 'text';
                const val = values[name] ?? field.default ?? '';

                if (type === 'toggle') {
                  return el(ToggleControl, {
                    key: name,
                    label,
                    checked: !!val,
                    onChange: (v) => s({ values: { ...values, [name]: v } }),
                  });
                }
                if (type === 'textarea') {
                  return el(TextareaControl, {
                    key: name,
                    label,
                    value: val,
                    onChange: (v) => s({ values: { ...values, [name]: v } }),
                  });
                }
                if (type === 'url') {
                  return el(
                    'div',
                    { key: name, style: { marginBottom: 16 } },
                    el('label', { style: { display: 'block', marginBottom: 4, fontSize: 11 } }, label),
                    el(URLInput, {
                      value: val,
                      onChange: (v) => s({ values: { ...values, [name]: v } }),
                      isFullWidth: true,
                      hasBorder: true,
                    }),
                  );
                }
                return el(TextControl, {
                  key: name,
                  label,
                  value: val,
                  onChange: (v) => s({ values: { ...values, [name]: v } }),
                });
              }),
            ),
        ),
    });
  },
  save: () => null,
});
