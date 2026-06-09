<?php
// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 注册词汇表自定义文章类型
 */
function lite_glossary_register_post_type() {
    $labels = array(
        'name'                  => _x( '词汇表', 'Post Type General Name', 'lite-glossary' ),
        'singular_name'         => _x( '词汇表术语', 'Post Type Singular Name', 'lite-glossary' ),
        'menu_name'             => __( '词汇表', 'lite-glossary' ),
        'name_admin_bar'        => __( '词汇表术语', 'lite-glossary' ),
        'archives'              => __( '词汇表归档', 'lite-glossary' ),
        'attributes'            => __( '词汇表属性', 'lite-glossary' ),
        'parent_item_colon'     => __( '父词汇表术语:', 'lite-glossary' ),
        'all_items'             => __( '所有词汇表术语', 'lite-glossary' ),
        'add_new_item'          => __( '添加新词汇表术语', 'lite-glossary' ),
        'add_new'               => __( '添加新', 'lite-glossary' ),
        'new_item'              => __( '新词汇表术语', 'lite-glossary' ),
        'edit_item'             => __( '编辑词汇表术语', 'lite-glossary' ),
        'update_item'           => __( '更新词汇表术语', 'lite-glossary' ),
        'view_item'             => __( '查看词汇表术语', 'lite-glossary' ),
        'view_items'            => __( '查看词汇表术语', 'lite-glossary' ),
        'search_items'          => __( '搜索词汇表术语', 'lite-glossary' ),
        'not_found'             => __( '未找到', 'lite-glossary' ),
        'not_found_in_trash'    => __( '回收站中未找到', 'lite-glossary' ),
        'featured_image'        => __( '特色图片', 'lite-glossary' ),
        'set_featured_image'    => __( '设置特色图片', 'lite-glossary' ),
        'remove_featured_image' => __( '移除特色图片', 'lite-glossary' ),
        'use_featured_image'    => __( '用作特色图片', 'lite-glossary' ),
        'insert_into_item'      => __( '插入到词汇表术语', 'lite-glossary' ),
        'uploaded_to_this_item' => __( '上传到此词汇表术语', 'lite-glossary' ),
        'items_list'            => __( '词汇表术语列表', 'lite-glossary' ),
        'items_list_navigation' => __( '词汇表术语列表导航', 'lite-glossary' ),
        'filter_items_list'     => __( '筛选词汇表术语列表', 'lite-glossary' ),
    );
    
    $args = array(
        'label'                 => __( '词汇表术语', 'lite-glossary' ),
        'description'           => __( '用于显示工具提示的词汇表术语', 'lite-glossary' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 20,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'rewrite'               => array( 'slug' => 'glossary' ),
    );
    
    register_post_type( 'glossary', $args );
}
add_action( 'init', 'lite_glossary_register_post_type' );