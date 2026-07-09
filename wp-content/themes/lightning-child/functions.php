<?php
/**
 * Lightning Child theme functions
 *
 * @package lightning
 */

/************************************************
 * Google Fonts 読み込み
 * 大見出し: Noto Serif JP / 本文・ナビ・ボタン: Noto Sans JP
 */
add_action(
	'wp_head',
	function () {
		echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
		echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	},
	1
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style(
			'slowhand-google-fonts',
			'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&family=Noto+Serif+JP:wght@600;700&display=swap',
			array(),
			null
		);
	}
);

/************************************************
 * 独自CSSファイルの読み込み処理
 *
 * 主に CSS を SASS で 書きたい人用です。 素の CSS を直接書くなら style.css に記載してかまいません.
 */

// 独自のCSSファイル（assets/css/）を読み込む場合は true に変更してください.
$my_lightning_additional_css = false;

if ( $my_lightning_additional_css ) {
	// 公開画面側のCSSの読み込み.
	add_action(
		'wp_enqueue_scripts',
		function() {
			wp_enqueue_style(
				'my-lightning-custom',
				get_stylesheet_directory_uri() . '/assets/css/style.css',
				array( 'lightning-design-style' ),
				filemtime( dirname( __FILE__ ) . '/assets/css/style.css' )
			);
		}
	);
	// 編集画面側のCSSの読み込み.
	add_action(
		'enqueue_block_editor_assets',
		function() {
			wp_enqueue_style(
				'my-lightning-editor-custom',
				get_stylesheet_directory_uri() . '/assets/css/editor.css',
				array( 'wp-edit-blocks', 'lightning-gutenberg-editor' ),
				filemtime( dirname( __FILE__ ) . '/assets/css/editor.css' )
			);
		}
	);
}

/************************************************
 * 独自の処理を必要に応じて書き足します
 */

/* 子テーマ style.css を更新時刻つきで読み込む */
add_filter(
	'style_loader_src',
	function( $src, $handle ) {
		if ( 'lightning-theme-style' !== $handle ) {
			return $src;
		}

		$style_path = get_stylesheet_directory() . '/style.css';

		if ( ! file_exists( $style_path ) ) {
			return $src;
		}

		return add_query_arg( 'ver', filemtime( $style_path ), get_stylesheet_uri() );
	},
	10,
	2
);


/* 固定ヘッダーを解除 */
add_filter( 'lightning_headfix_enable', 'lightning_headfix_disabel');
function lightning_headfix_disabel(){
    return false;
}

/* TOPページ用 NEWS ショートコード */
add_shortcode( 'slowhand_top_news', 'slowhand_child_top_news_shortcode' );
function slowhand_child_top_news_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'count'     => 6,
			'category'  => '',
			'show_date' => 'false',
		),
		$atts,
		'slowhand_top_news'
	);

	$count     = max( 1, min( 12, absint( $atts['count'] ) ) );
	$show_date = filter_var( $atts['show_date'], FILTER_VALIDATE_BOOLEAN );

	$query_args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $count,
		'ignore_sticky_posts' => true,
	);

	if ( '' !== $atts['category'] ) {
		$query_args['category_name'] = sanitize_title( $atts['category'] );
	}

	$news_query = new WP_Query( $query_args );

	if ( ! $news_query->have_posts() ) {
		return '';
	}

	ob_start();
	?>
	<div class="top-news-posts">
		<?php
		while ( $news_query->have_posts() ) :
			$news_query->the_post();
			$categories     = get_the_category();
			$category       = ! empty( $categories ) ? $categories[0] : null;
			$category_name  = $category ? $category->name : '';
			$category_slug  = $category ? $category->slug : 'uncategorized';
			$thumbnail_id   = get_post_thumbnail_id();
			$thumbnail_alt  = $thumbnail_id ? get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ) : '';
			$thumbnail_alt  = '' !== $thumbnail_alt ? $thumbnail_alt : get_the_title();
			?>
			<article class="top-news-card top-news-card-category-<?php echo esc_attr( $category_slug ); ?>">
				<a class="top-news-card-image-link" href="<?php the_permalink(); ?>">
					<figure class="top-news-card-image">
						<?php if ( $category_name ) : ?>
							<span class="news-label"><?php echo esc_html( $category_name ); ?></span>
						<?php endif; ?>
						<?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail(
								'large',
								array(
									'alt' => esc_attr( $thumbnail_alt ),
								)
							);
						}
						?>
					</figure>
				</a>
				<h3 class="news-card-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h3>
				<?php if ( $show_date ) : ?>
					<p class="top-news-date"><?php echo esc_html( get_the_date() ); ?></p>
				<?php endif; ?>
			</article>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</div>
	<?php

	return ob_get_clean();
}

/* フッター中央ウィジェット用 メニューショートコード */
add_shortcode( 'slowhand_footer_menu', 'slowhand_child_footer_menu_shortcode' );
function slowhand_child_footer_menu_shortcode() {
	return slowhand_child_get_footer_menu_html();
}

function slowhand_child_get_footer_menu_html() {
	if ( ! has_nav_menu( 'footer-nav' ) || ! class_exists( 'Slowhand_Child_Footer_Menu_Walker' ) ) {
		return '';
	}

	return wp_nav_menu(
		array(
			'theme_location' => 'footer-nav',
			'container'      => 'nav',
			'container_class' => 'slowhand-footer-menu',
			'menu_class'     => 'slowhand-footer-menu-list',
			'depth'          => 2,
			'echo'           => false,
			'fallback_cb'    => false,
			'walker'         => new Slowhand_Child_Footer_Menu_Walker(),
		)
	);
}

if ( ! class_exists( 'Slowhand_Child_Footer_Menu_Walker' ) ) {
	class Slowhand_Child_Footer_Menu_Walker extends Walker_Nav_Menu {
		public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
			$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
			$classes[] = 'slowhand-footer-menu-item';

			if ( 0 < $depth ) {
				$classes[] = 'slowhand-footer-menu-child';
			}

			$class_names = implode( ' ', array_map( 'sanitize_html_class', array_filter( $classes ) ) );
			$title       = apply_filters( 'the_title', $item->title, $item->ID );
			$url         = trim( (string) $item->url );

			$output .= '<li class="' . esc_attr( $class_names ) . '">';

			if ( '#' === $url || '' === $url ) {
				$output .= '<span class="slowhand-footer-menu-label">' . esc_html( $title ) . '</span>';
				return;
			}

			$atts = array(
				'href' => $url,
			);

			if ( ! empty( $item->target ) ) {
				$atts['target'] = $item->target;
			}

			if ( ! empty( $item->xfn ) ) {
				$atts['rel'] = $item->xfn;
			}

			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( 'href' === $attr ) {
					$attributes .= ' href="' . esc_url( $value ) . '"';
					continue;
				}

				$attributes .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
			}

			$output .= '<a' . $attributes . '>' . esc_html( $title ) . '</a>';
		}
	}
}

/* フッター中央ウィジェットに Footer Navigation を表示する */
add_filter( 'dynamic_sidebar_params', 'slowhand_child_add_footer_menu_to_center_widget' );
function slowhand_child_add_footer_menu_to_center_widget( $params ) {
	static $footer_center_menu_added = false;

	if (
		$footer_center_menu_added ||
		empty( $params[0]['id'] ) ||
		'footer-widget-2' !== $params[0]['id']
	) {
		return $params;
	}

	$menu_html = slowhand_child_get_footer_menu_html();

	if ( '' === $menu_html ) {
		return $params;
	}

	$params[0]['after_widget'] .= $menu_html;
	$footer_center_menu_added   = true;

	return $params;
}

/* LINE相談フローティングボタン（全ページ共通） */
add_action( 'wp_footer', 'slowhand_child_render_line_buttons' );
function slowhand_child_render_line_buttons() {
	$buttons = array(
		array(
			'brand' => 'slowhand',
			'label' => 'SLOWHAND',
			'url'   => 'https://line.me/R/ti/p/@096nzseo',
		),
		array(
			'brand' => 'yumemido',
			'label' => 'ゆめみ堂',
			'url'   => 'https://lin.ee/9IC25cY',
		),
	);
	?>
	<div class="slowhand-line-buttons">
		<?php foreach ( $buttons as $button ) : ?>
			<a
				class="slowhand-line-button slowhand-line-button-<?php echo esc_attr( $button['brand'] ); ?>"
				href="<?php echo esc_url( $button['url'] ); ?>"
				target="_blank"
				rel="noopener noreferrer"
			>
				<span class="slowhand-line-button-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24"><path d="M12 3C6.48 3 2 6.69 2 11.2c0 4.05 3.58 7.44 8.42 8.08.33.07.77.22.88.5.1.26.07.66.03.92l-.14.86c-.04.26-.2 1 .87.55s5.78-3.4 7.89-5.83C21.44 14.6 22 13 22 11.2 22 6.69 17.52 3 12 3z"/></svg>
				</span>
				<span class="slowhand-line-button-label"><?php echo esc_html( $button['label'] ); ?><br>LINEでご予約</span>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
}
