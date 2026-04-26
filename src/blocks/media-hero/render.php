<?php
/**
 * Render the Media Hero block on the front end.
 *
 * @param array $attributes Block attributes.
 */

$dm  = ! empty( $attributes['desktopMedia'] ) ? $attributes['desktopMedia'] : [];
$mm  = ! empty( $attributes['mobileMedia'] ) ? $attributes['mobileMedia'] : [];
$dt  = ! empty( $attributes['desktopTitle'] ) ? $attributes['desktopTitle'] : '';
$ds  = ! empty( $attributes['desktopSubtitle'] ) ? $attributes['desktopSubtitle'] : '';
$dbt = ! empty( $attributes['desktopButtonText'] ) ? $attributes['desktopButtonText'] : '';
$dbu = ! empty( $attributes['desktopButtonUrl'] ) ? $attributes['desktopButtonUrl'] : '';
$mt  = ! empty( $attributes['mobileTitle'] ) ? $attributes['mobileTitle'] : '';
$ms  = ! empty( $attributes['mobileSubtitle'] ) ? $attributes['mobileSubtitle'] : '';
$mbt = ! empty( $attributes['mobileButtonText'] ) ? $attributes['mobileButtonText'] : '';
$mbu = ! empty( $attributes['mobileButtonUrl'] ) ? $attributes['mobileButtonUrl'] : '';

if ( ! function_exists( 'twc_mh_render_media' ) ) {
    function twc_mh_render_media( $media ) {
        if ( empty( $media['url'] ) ) {
            return '';
        }

        if ( ( $media['type'] ?? '' ) === 'video' ) {
            $poster = ! empty( $media['poster'] ) ? ' poster="' . esc_url( $media['poster'] ) . '"' : '';
            return '<video src="' . esc_url( $media['url'] ) . '"' . $poster . ' muted autoplay loop playsinline></video>';
        }

        return '<img src="' . esc_url( $media['url'] ) . '" alt="' . esc_attr( $media['alt'] ?? '' ) . '">';
    }
}

if ( ! function_exists( 'twc_mh_render_version' ) ) {
    function twc_mh_render_version( $class, $media, $title, $subtitle, $button_text, $button_url ) {
        $media_html = twc_mh_render_media( $media );

        if ( empty( $media_html ) && empty( $title ) && empty( $subtitle ) && empty( $button_text ) ) {
            return '';
        }

        $button = '';
        if ( ! empty( $button_text ) ) {
            $button = ! empty( $button_url )
                ? '<a href="' . esc_url( $button_url ) . '" class="twc-media-hero__button">' . esc_html( $button_text ) . '</a>'
                : '<span class="twc-media-hero__button">' . esc_html( $button_text ) . '</span>';
        }

        return '<div class="twc-media-hero__version ' . esc_attr( $class ) . '">'
            . '<div class="twc-media-hero__container">'
                . '<div class="twc-media-hero__media">' . $media_html . '</div>'
                . '<div class="twc-media-hero__content">'
                    . ( $title ? '<h2 class="twc-media-hero__title">' . esc_html( $title ) . '</h2>' : '' )
                    . ( $subtitle ? '<p class="twc-media-hero__subtitle">' . wp_kses_post( $subtitle ) . '</p>' : '' )
                    . $button
                . '</div>'
            . '</div>'
        . '</div>';
    }
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'twc-media-hero' ) );

echo '<div ' . $wrapper . '>';
echo twc_mh_render_version( 'twc-media-hero__desktop', $dm, $dt, $ds, $dbt, $dbu );
echo twc_mh_render_version( 'twc-media-hero__mobile', $mm, $mt, $ms, $mbt, $mbu );
echo '</div>';
