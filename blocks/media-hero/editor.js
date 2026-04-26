(function() {
    var registerBlockType = wp.blocks.registerBlockType;
    var __ = wp.i18n.__;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var RichText = wp.blockEditor.RichText;
    var MediaUpload = wp.blockEditor.MediaUpload;
    var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
    var BlockControls = wp.blockEditor.BlockControls;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var Button = wp.components.Button;
    var ToolbarGroup = wp.components.ToolbarGroup;
    var ToolbarButton = wp.components.ToolbarButton;
    var PanelBody = wp.components.PanelBody;
    var BaseControl = wp.components.BaseControl;
    var useState = wp.element.useState;
    var Fragment = wp.element.Fragment;
    var el = wp.element.createElement;

    registerBlockType('twc/media-hero', {
        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            var viewModeState = useState('desktop');
            var viewMode = viewModeState[0];
            var setViewMode = viewModeState[1];

            var isDesktop = viewMode === 'desktop';
            var prefix = isDesktop ? 'desktop' : 'mobile';

            var media = attributes[prefix + 'Media'] || {};
            var title = attributes[prefix + 'Title'] || '';
            var subtitle = attributes[prefix + 'Subtitle'] || '';
            var buttonText = attributes[prefix + 'ButtonText'] || '';
            var buttonUrl = attributes[prefix + 'ButtonUrl'] || '';

            function update(field, value) {
                var attrs = {};
                attrs[prefix + field] = value;
                setAttributes(attrs);
            }

            var blockProps = useBlockProps({ className: 'twc-media-hero' });

            return el(Fragment, null,
                el(BlockControls, null,
                    el(ToolbarGroup, null,
                        el(ToolbarButton, {
                            icon: 'desktop',
                            label: __('Desktop', 'twc'),
                            isActive: isDesktop,
                            onClick: function() { setViewMode('desktop'); }
                        }),
                        el(ToolbarButton, {
                            icon: 'smartphone',
                            label: __('Mobile', 'twc'),
                            isActive: !isDesktop,
                            onClick: function() { setViewMode('mobile'); }
                        })
                    )
                ),
                el(InspectorControls, null,
                    el(PanelBody, { title: __('Button Link', 'twc'), initialOpen: true },
                        el(BaseControl, { label: __('URL', 'twc') },
                            el('input', {
                                type: 'url',
                                value: buttonUrl,
                                onChange: function(e) { update('ButtonUrl', e.target.value); },
                                className: 'components-text-control__input',
                                placeholder: 'https://…'
                            })
                        )
                    )
                ),
                el('div', blockProps,
                    el('div', {
                        className: 'twc-media-hero__preview' + (!isDesktop ? ' twc-media-hero__preview--mobile' : '')
                    },
                        el('div', { className: 'twc-media-hero__container' },
                            el('div', { className: 'twc-media-hero__media' },
                                el(MediaUploadCheck, null,
                                    el(MediaUpload, {
                                        onSelect: function(obj) {
                                            update('Media', {
                                                url: obj.url,
                                                id: obj.id,
                                                type: obj.type,
                                                alt: obj.alt || '',
                                                poster: obj.type === 'video' ? (obj.image ? obj.image.src : '') : ''
                                            });
                                        },
                                        allowedTypes: ['image', 'video'],
                                        value: media.id,
                                        render: function(obj) {
                                            var open = obj.open;
                                            if (media.url) {
                                                return el('div', { className: 'twc-media-hero__media-wrap' },
                                                    media.type === 'video'
                                                        ? el('video', {
                                                            src: media.url,
                                                            poster: media.poster || '',
                                                            muted: true,
                                                            autoPlay: true,
                                                            loop: true,
                                                            playsInline: true
                                                        })
                                                        : el('img', { src: media.url, alt: media.alt || '' }),
                                                    el('div', { className: 'twc-media-hero__media-actions' },
                                                        el(Button, { onClick: open, variant: 'secondary', isSmall: true },
                                                            __('Replace', 'twc')
                                                        ),
                                                        el(Button, {
                                                            onClick: function() { update('Media', {}); },
                                                            variant: 'link',
                                                            isDestructive: true,
                                                            isSmall: true
                                                        }, __('Remove', 'twc'))
                                                    )
                                                );
                                            }
                                            return el('button', {
                                                onClick: open,
                                                className: 'twc-media-hero__media-placeholder'
                                            },
                                                el('span', { className: 'dashicons dashicons-format-image' }),
                                                el('span', null, __('Select Image or Video', 'twc'))
                                            );
                                        }
                                    })
                                )
                            ),
                            el('div', { className: 'twc-media-hero__content' },
                                el(RichText, {
                                    tagName: 'h2',
                                    className: 'twc-media-hero__title',
                                    value: title,
                                    onChange: function(v) { update('Title', v); },
                                    placeholder: __('Title', 'twc'),
                                    allowedFormats: []
                                }),
                                el(RichText, {
                                    tagName: 'p',
                                    className: 'twc-media-hero__subtitle',
                                    value: subtitle,
                                    onChange: function(v) { update('Subtitle', v); },
                                    placeholder: __('Subtitle text…', 'twc'),
                                    allowedFormats: ['core/bold', 'core/italic']
                                }),
                                el('div', { className: 'twc-media-hero__btn-wrap' },
                                    el(RichText, {
                                        tagName: 'span',
                                        className: 'twc-media-hero__button',
                                        value: buttonText,
                                        onChange: function(v) { update('ButtonText', v); },
                                        placeholder: __('Button text', 'twc'),
                                        allowedFormats: []
                                    })
                                )
                            )
                        )
                    ),
                    el('div', { className: 'twc-media-hero__view-label' },
                        isDesktop
                            ? __('Editing: Desktop view', 'twc')
                            : __('Editing: Mobile view', 'twc')
                    )
                )
            );
        },
        save: function() { return null; }
    });
})();
