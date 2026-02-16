<?php
// 防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 添加管理菜单
 */
function lite_glossary_add_admin_menu() {
    add_menu_page(
        __( '轻量级词汇表', 'lite-glossary' ),
        __( '轻量级词汇表', 'lite-glossary' ),
        'manage_options',
        'lite-glossary',
        'lite_glossary_admin_page',
        'dashicons-book',
        20
    );
}
add_action( 'admin_menu', 'lite_glossary_add_admin_menu' );

/**
 * 管理页面回调函数
 */
function lite_glossary_admin_page() {
    // 处理单个删除操作 - 必须在任何输出之前执行
    if ( isset( $_GET['action'] ) && $_GET['action'] == 'lite_glossary_delete' && isset( $_GET['term_id'] ) ) {
        // 验证nonce
        if ( wp_verify_nonce( $_GET['_wpnonce'], 'lite_glossary_delete' ) ) {
            $term_id = intval( $_GET['term_id'] );
            if ( $term_id > 0 && wp_delete_post( $term_id, true ) ) {
                delete_transient( 'lite_glossary_terms' );
                // 重定向回管理页面并显示成功消息
                wp_redirect( add_query_arg( array(
                    'page' => 'lite-glossary',
                    'single_deleted' => 1
                ), admin_url( 'admin.php' ) ) );
                exit;
            }
        } else {
            wp_die( '安全验证失败' );
        }
    }
    
    // 处理批量删除操作 - 必须在任何输出之前执行
    if ( isset( $_POST['lite_glossary_bulk_delete'] ) ) {
        // 1. 安全检查：验证 Nonce 随机数，防止 CSRF 攻击
        if ( ! isset( $_POST['lite_glossary_nonce'] ) || ! wp_verify_nonce( $_POST['lite_glossary_nonce'], 'lite_glossary_bulk_delete' ) ) {
            wp_die( '安全验证失败' );
        }

        // 2. 权限检查：确保当前用户有删除权限
        if ( ! current_user_can( 'delete_posts' ) ) {
            wp_die( '你没有权限执行此操作' );
        }

        // 3. 获取 ID 并执行删除
        if ( ! empty( $_POST['term_ids'] ) && is_array( $_POST['term_ids'] ) ) {
            // 使用 array_map 处理 ID，确保数据全是数字
            $term_ids = array_map( 'intval', $_POST['term_ids'] );

            $deleted = 0;
            foreach ( $term_ids as $term_id ) {
                if ( $term_id > 0 && wp_delete_post( $term_id, true ) ) {
                    $deleted++;
                }
            }

            // 清除缓存
            delete_transient( 'lite_glossary_terms' );

            // 4. 操作完成后重定向并添加反馈参数
            wp_redirect( add_query_arg( array(
                'page' => 'lite-glossary',
                'bulk_deleted' => $deleted
            ), admin_url( 'admin.php' ) ) );
            exit;
        } else {
            // 没有选择术语，重定向回管理页面
            wp_redirect( add_query_arg( array(
                'page' => 'lite-glossary',
                'no_terms_selected' => 1
            ), admin_url( 'admin.php' ) ) );
            exit;
        }
    }
    
    // 显示批量删除的反馈提示 - 这部分在所有重定向之后执行
    if ( isset( $_GET['bulk_deleted'] ) && intval( $_GET['bulk_deleted'] ) > 0 ) {
        echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( __( '成功删除 %d 个术语。', 'lite-glossary' ), intval( $_GET['bulk_deleted'] ) ) . '</p></div>';
    } elseif ( isset( $_GET['single_deleted'] ) ) {
        echo '<div class="notice notice-success is-dismissible"><p>' . __( '术语已成功删除。', 'lite-glossary' ) . '</p></div>';
    } elseif ( isset( $_GET['no_terms_selected'] ) ) {
        echo '<div class="notice notice-error is-dismissible"><p>' . __( '请选择要删除的术语。', 'lite-glossary' ) . '</p></div>';
    }
    
    // 处理 CSV 导入
    if ( isset( $_POST['lite_glossary_import'] ) && isset( $_POST['lite_glossary_csv'] ) ) {
        if ( wp_verify_nonce( $_POST['lite_glossary_nonce'], 'lite_glossary_import' ) ) {
            lite_glossary_handle_csv_import();
        }
    }
    
    // 如果设置更新，清除缓存
    if ( isset( $_POST['lite_glossary_settings'] ) ) {
        if ( wp_verify_nonce( $_POST['lite_glossary_nonce'], 'lite_glossary_settings' ) ) {
            delete_transient( 'lite_glossary_terms' );
        }
    }
    ?>
    <div class="wrap">
        <h1><?php _e( '轻量级词汇表设置', 'lite-glossary' ); ?></h1>
        
        <h2 class="nav-tab-wrapper">
            <a href="#settings" class="nav-tab nav-tab-active"><?php _e( '设置', 'lite-glossary' ); ?></a>
            <a href="#import" class="nav-tab"><?php _e( '导入', 'lite-glossary' ); ?></a>
            <a href="#manage" class="nav-tab"><?php _e( '术语管理', 'lite-glossary' ); ?></a>
        </h2>
        
        <div id="settings" class="tab-content">
            <form method="post" action="">
                <?php wp_nonce_field( 'lite_glossary_settings', 'lite_glossary_nonce' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <?php _e( '高亮选项', 'lite-glossary' ); ?>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="lite_glossary_first_occurrence_only" value="1" <?php checked( get_option( 'lite_glossary_first_occurrence_only', false ), 1 ); ?>>
                                <?php _e( '仅高亮内容中首次出现的术语', 'lite-glossary' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <input type="hidden" name="lite_glossary_settings" value="1">
                <?php submit_button( __( '保存设置', 'lite-glossary' ) ); ?>
            </form>
        </div>
        
        <div id="import" class="tab-content" style="display: none;">
            <h2><?php _e( '导入词汇表术语', 'lite-glossary' ); ?></h2>
            <p><?php _e( '在下方粘贴 CSV 文本（格式：术语1｜术语2,解释）。已存在的术语将被更新。', 'lite-glossary' ); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field( 'lite_glossary_import', 'lite_glossary_nonce' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <?php _e( 'CSV 文本', 'lite-glossary' ); ?>
                        </th>
                        <td>
                            <textarea name="lite_glossary_csv" rows="10" cols="50" class="large-text"></textarea>
                            <p class="description"><?php _e( '示例：HHX｜张三,张三 - 一班\nABC｜李四,李四 - 二班', 'lite-glossary' ); ?></p>
                        </td>
                    </tr>
                </table>
                
                <input type="hidden" name="lite_glossary_import" value="1">
                <?php submit_button( __( '导入术语', 'lite-glossary' ) ); ?>
            </form>
        </div>
        
        <div id="manage" class="tab-content" style="display: none;">
            <h2><?php _e( '术语管理', 'lite-glossary' ); ?></h2>
            <p><?php _e( '以下是所有已添加的词汇表术语，您可以编辑或删除它们。', 'lite-glossary' ); ?></p>
            
            <div class="tablenav top">
                <div class="alignleft actions">
                    <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=glossary' ) ); ?>" class="button button-primary"><?php _e( '新增术语', 'lite-glossary' ); ?></a>
                </div>
                <br class="clear">
            </div>
            
            <?php
            // 获取所有词汇表术语
            $args = array(
                'post_type'      => 'glossary',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            );
            
            $query = new WP_Query( $args );
            
            if ( $query->have_posts() ) {
                ?>
                <form method="post" action="" id="terms-form">
                    <?php wp_nonce_field( 'lite_glossary_bulk_delete', 'lite_glossary_nonce' ); ?>
                    <div class="tablenav top">
                        <div class="alignleft actions">
                            <input type="submit" name="lite_glossary_bulk_delete" class="button button-danger" value="<?php _e( '批量删除', 'lite-glossary' ); ?>" onclick="return confirm('<?php _e( '确定要删除选中的术语吗？', 'lite-glossary' ); ?>');">
                        </div>
                        <br class="clear">
                    </div>
                    <table class="widefat fixed" cellspacing="0">
                        <thead>
                            <tr>
                                <th scope="col" class="manage-column column-cb check-column">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th scope="col" class="manage-column column-title"><?php _e( '术语', 'lite-glossary' ); ?></th>
                                <th scope="col" class="manage-column column-content"><?php _e( '解释', 'lite-glossary' ); ?></th>
                                <th scope="col" class="manage-column column-actions"><?php _e( '操作', 'lite-glossary' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ( $query->have_posts() ) {
                                $query->the_post();
                                $term_id = get_the_ID();
                                $term_title = get_the_title();
                                $term_content = wp_strip_all_tags( get_the_content() );
                                $term_content = mb_strlen( $term_content ) > 50 ? mb_substr( $term_content, 0, 50 ) . '...' : $term_content;
                                ?>
                                <tr>
                                    <td class="check-column">
                                        <input type="checkbox" name="term_ids[]" value="<?php echo esc_attr( $term_id ); ?>">
                                    </td>
                                    <td class="column-title">
                                        <strong><?php echo esc_html( $term_title ); ?></strong>
                                    </td>
                                    <td class="column-content">
                                        <?php echo esc_html( $term_content ); ?>
                                    </td>
                                    <td class="column-actions">
                                        <a href="<?php echo esc_url( get_edit_post_link( $term_id ) ); ?>" class="button button-primary"><?php _e( '编辑', 'lite-glossary' ); ?></a>
                                        <a href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'lite_glossary_delete', 'term_id' => $term_id ), admin_url( 'admin.php?page=lite-glossary' ) ), 'lite_glossary_delete' ); ?>" class="button button-danger" onclick="return confirm('<?php _e( '确定要删除这个术语吗？', 'lite-glossary' ); ?>');"><?php _e( '删除', 'lite-glossary' ); ?></a>
                                    </td>
                                </tr>
                                <?php
                            }
                            wp_reset_postdata();
                            ?>
                        </tbody>
                    </table>
                </form>
                
                <script>
                // 全选/取消全选功能
                document.getElementById('select-all').addEventListener('change', function() {
                    var checkboxes = document.querySelectorAll('input[name="term_ids[]"]');
                    checkboxes.forEach(function(checkbox) {
                        checkbox.checked = document.getElementById('select-all').checked;
                    });
                });
                </script>
                <?php
            } else {
                ?>
                <div class="notice notice-info is-dismissible">
                    <p><?php _e( '还没有添加任何词汇表术语。', 'lite-glossary' ); ?></p>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 获取所有标签页
        var tabs = document.querySelectorAll('.nav-tab');
        
        // 为每个标签页添加点击事件监听器
        tabs.forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // 移除所有标签页的活动类
                tabs.forEach(function(t) {
                    t.classList.remove('nav-tab-active');
                });
                
                // 为点击的标签页添加活动类
                this.classList.add('nav-tab-active');
                
                // 隐藏所有标签页内容
                var tabContents = document.querySelectorAll('.tab-content');
                tabContents.forEach(function(content) {
                    content.style.display = 'none';
                });
                
                // 显示选中的标签页内容
                var tabId = this.getAttribute('href');
                var selectedContent = document.querySelector(tabId);
                if (selectedContent) {
                    selectedContent.style.display = 'block';
                }
            });
        });
    });
    </script>
    <?php
}

/**
 * 处理 CSV 导入
 */
function lite_glossary_handle_csv_import() {
    if ( ! isset( $_POST['lite_glossary_csv'] ) ) {
        return;
    }
    
    $csv_text = trim( $_POST['lite_glossary_csv'] );
    
    if ( empty( $csv_text ) ) {
        return;
    }
    
    // 将 CSV 文本分割成行
    $lines = explode( "\n", $csv_text );
    
    $imported = 0;
    $updated = 0;
    
    foreach ( $lines as $line ) {
        $line = trim( $line );
        
        if ( empty( $line ) ) {
            continue;
        }
        
        // 将行分割成部分
        $parts = explode( ',', $line, 2 );
        
        if ( count( $parts ) < 2 ) {
            continue;
        }
        
        $abbreviation = trim( $parts[0] );
        $full_name = trim( $parts[1] );
        
        if ( empty( $abbreviation ) || empty( $full_name ) ) {
            continue;
        }
        
        // 检查术语是否已存在
        $existing_post = lite_glossary_get_existing_term( $abbreviation );
        
        if ( $existing_post ) {
            // 更新现有术语
            $post_id = wp_update_post( array(
                'ID'           => $existing_post->ID,
                'post_title'   => $abbreviation,
                'post_content' => $full_name,
                'post_type'    => 'glossary',
                'post_status'  => 'publish',
            ) );
            
            if ( $post_id ) {
                $updated++;
            }
        } else {
            // 创建新术语
            $post_id = wp_insert_post( array(
                'post_title'   => $abbreviation,
                'post_content' => $full_name,
                'post_type'    => 'glossary',
                'post_status'  => 'publish',
            ) );
            
            if ( $post_id ) {
                $imported++;
            }
        }
    }
    
    // 清除缓存
    delete_transient( 'lite_glossary_terms' );
    
    // 显示通知
    if ( $imported > 0 || $updated > 0 ) {
        $message = sprintf(
            __( '导入完成：%d 个新术语，%d 个更新术语', 'lite-glossary' ),
            $imported,
            $updated
        );
        
        add_action( 'admin_notices', function() use ( $message ) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html( $message ); ?></p>
            </div>
            <?php
        });
    }
}

/**
 * 根据标题获取现有词汇表术语
 */
function lite_glossary_get_existing_term( $title ) {
    $args = array(
        'post_type'      => 'glossary',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'title'          => $title,
        'fields'         => 'ids', // 仅获取文章 ID 以提高性能
    );
    
    $post_ids = get_posts( $args );
    
    if ( ! empty( $post_ids ) ) {
        return get_post( $post_ids[0] );
    }
    
    return false;
}