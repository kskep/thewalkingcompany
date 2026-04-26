import { __ } from '@wordpress/i18n';
import {
    useBlockProps,
    RichText,
    MediaUpload,
    MediaUploadCheck,
    BlockControls,
    InspectorControls,
} from '@wordpress/block-editor';
import {
    Button,
    ToolbarGroup,
    ToolbarButton,
    PanelBody,
    BaseControl,
} from '@wordpress/components';
import { useState, Fragment } from '@wordpress/element';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
    const [viewMode, setViewMode] = useState('desktop');
    const isDesktop = viewMode === 'desktop';
    const prefix = isDesktop ? 'desktop' : 'mobile';

    const media = attributes[`${prefix}Media`] || {};
    const title = attributes[`${prefix}Title`] || '';
    const subtitle = attributes[`${prefix}Subtitle`] || '';
    const buttonText = attributes[`${prefix}ButtonText`] || '';
    const buttonUrl = attributes[`${prefix}ButtonUrl`] || '';

    const update = (field, value) =>
        setAttributes({ [`${prefix}${field}`]: value });

    const onSelectMedia = (obj) => {
        update('Media', {
            url: obj.url,
            id: obj.id,
            type: obj.type,
            alt: obj.alt || '',
            poster: obj.type === 'video' ? obj.image?.src || '' : '',
        });
    };

    const blockProps = useBlockProps({ className: 'twc-media-hero' });

    return (
        <Fragment>
            <BlockControls>
                <ToolbarGroup>
                    <ToolbarButton
                        icon="desktop"
                        label={__('Desktop', 'twc')}
                        isActive={isDesktop}
                        onClick={() => setViewMode('desktop')}
                    />
                    <ToolbarButton
                        icon="smartphone"
                        label={__('Mobile', 'twc')}
                        isActive={!isDesktop}
                        onClick={() => setViewMode('mobile')}
                    />
                </ToolbarGroup>
            </BlockControls>

            <InspectorControls>
                <PanelBody title={__('Button Link', 'twc')} initialOpen={true}>
                    <BaseControl label={__('URL', 'twc')}>
                        <input
                            type="url"
                            value={buttonUrl}
                            onChange={(e) => update('ButtonUrl', e.target.value)}
                            className="components-text-control__input"
                            placeholder="https://…"
                        />
                    </BaseControl>
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                <div className={`twc-media-hero__preview${!isDesktop ? ' twc-media-hero__preview--mobile' : ''}`}>
                    <div className="twc-media-hero__container">
                        <div className="twc-media-hero__media">
                            <MediaUploadCheck>
                                <MediaUpload
                                    onSelect={onSelectMedia}
                                    allowedTypes={['image', 'video']}
                                    value={media.id}
                                    render={({ open }) =>
                                        media.url ? (
                                            <div className="twc-media-hero__media-wrap">
                                                {media.type === 'video' ? (
                                                    <video
                                                        src={media.url}
                                                        poster={media.poster}
                                                        muted
                                                        autoPlay
                                                        loop
                                                        playsInline
                                                    />
                                                ) : (
                                                    <img src={media.url} alt={media.alt} />
                                                )}
                                                <div className="twc-media-hero__media-actions">
                                                    <Button onClick={open} variant="secondary" isSmall>
                                                        {__('Replace', 'twc')}
                                                    </Button>
                                                    <Button
                                                        onClick={() => update('Media', {})}
                                                        variant="link"
                                                        isDestructive
                                                        isSmall
                                                    >
                                                        {__('Remove', 'twc')}
                                                    </Button>
                                                </div>
                                            </div>
                                        ) : (
                                            <button onClick={open} className="twc-media-hero__media-placeholder">
                                                <span className="dashicons dashicons-format-image" />
                                                <span>{__('Select Image or Video', 'twc')}</span>
                                            </button>
                                        )
                                    }
                                />
                            </MediaUploadCheck>
                        </div>

                        <div className="twc-media-hero__content">
                            <RichText
                                tagName="h2"
                                className="twc-media-hero__title"
                                value={title}
                                onChange={(v) => update('Title', v)}
                                placeholder={__('Title', 'twc')}
                                allowedFormats={[]}
                            />
                            <RichText
                                tagName="p"
                                className="twc-media-hero__subtitle"
                                value={subtitle}
                                onChange={(v) => update('Subtitle', v)}
                                placeholder={__('Subtitle text…', 'twc')}
                                allowedFormats={['core/bold', 'core/italic']}
                            />
                            <div className="twc-media-hero__btn-wrap">
                                <RichText
                                    tagName="span"
                                    className="twc-media-hero__button"
                                    value={buttonText}
                                    onChange={(v) => update('ButtonText', v)}
                                    placeholder={__('Button text', 'twc')}
                                    allowedFormats={[]}
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div className="twc-media-hero__view-label">
                    {isDesktop
                        ? __('Editing: Desktop view', 'twc')
                        : __('Editing: Mobile view', 'twc')}
                </div>
            </div>
        </Fragment>
    );
}
