<?php

/**
 * A helper file for Dcat Admin, to provide autocomplete information to your IDE
 *
 * This file should not be included in your code, only analyzed by your IDE!
 *
 * @author jqh <841324345@qq.com>
 */
namespace Dcat\Admin {
    use Illuminate\Support\Collection;

    /**
     * @property Grid\Column|Collection id
     * @property Grid\Column|Collection username
     * @property Grid\Column|Collection name
     * @property Grid\Column|Collection roles
     * @property Grid\Column|Collection permissions
     * @property Grid\Column|Collection created_at
     * @property Grid\Column|Collection updated_at
     * @property Grid\Column|Collection avatar
     * @property Grid\Column|Collection user
     * @property Grid\Column|Collection method
     * @property Grid\Column|Collection path
     * @property Grid\Column|Collection ip
     * @property Grid\Column|Collection input
     * @property Grid\Column|Collection slug
     * @property Grid\Column|Collection version
     * @property Grid\Column|Collection alias
     * @property Grid\Column|Collection authors
     * @property Grid\Column|Collection enable
     * @property Grid\Column|Collection imported
     * @property Grid\Column|Collection config
     * @property Grid\Column|Collection require
     * @property Grid\Column|Collection require_dev
     * @property Grid\Column|Collection personnel_no
     * @property Grid\Column|Collection sex
     * @property Grid\Column|Collection work_status
     * @property Grid\Column|Collection department_name
     * @property Grid\Column|Collection position_name
     * @property Grid\Column|Collection mold_information_no
     * @property Grid\Column|Collection client_name
     * @property Grid\Column|Collection company_model
     * @property Grid\Column|Collection mold_make_detail_standard
     * @property Grid\Column|Collection actual_size
     * @property Grid\Column|Collection settle_size
     * @property Grid\Column|Collection price
     * @property Grid\Column|Collection properties
     * @property Grid\Column|Collection personnel_name
     * @property Grid\Column|Collection mold_maker_name
     * @property Grid\Column|Collection mold_category_parent_name
     * @property Grid\Column|Collection mold_category_child_name
     * @property Grid\Column|Collection log_user_name
     * @property Grid\Column|Collection check
     * @property Grid\Column|Collection status
     * @property Grid\Column|Collection mold_maker_no
     * @property Grid\Column|Collection pinyin
     * @property Grid\Column|Collection add_at
     * @property Grid\Column|Collection mold_category_name
     * @property Grid\Column|Collection raw_material_category_name
     * @property Grid\Column|Collection unit_name
     * @property Grid\Column|Collection supplier_no
     * @property Grid\Column|Collection supplier_name
     * @property Grid\Column|Collection contact
     * @property Grid\Column|Collection tel
     * @property Grid\Column|Collection fax
     * @property Grid\Column|Collection purchase_standard_name
     * @property Grid\Column|Collection color_name
     * @property Grid\Column|Collection raw_material_product_information_no
     * @property Grid\Column|Collection raw_material_product_information_name
     * @property Grid\Column|Collection unit
     * @property Grid\Column|Collection material_level
     * @property Grid\Column|Collection color
     * @property Grid\Column|Collection standard
     * @property Grid\Column|Collection change_coefficient
     * @property Grid\Column|Collection mold_type
     * @property Grid\Column|Collection out_num
     * @property Grid\Column|Collection product_feature
     * @property Grid\Column|Collection product_category_name
     * @property Grid\Column|Collection remark
     * @property Grid\Column|Collection check_user_name
     * @property Grid\Column|Collection purcahse_standard_name
     * @property Grid\Column|Collection storage_in_num
     * @property Grid\Column|Collection check_status
     * @property Grid\Column|Collection is_void
     * @property Grid\Column|Collection is_check
     * @property Grid\Column|Collection void_reason
     * @property Grid\Column|Collection sole_material_color_name
     * @property Grid\Column|Collection sole_material_name
     * @property Grid\Column|Collection carft_skill_name
     * @property Grid\Column|Collection client_model
     * @property Grid\Column|Collection craft_color_name
     * @property Grid\Column|Collection standard_detail_name
     * @property Grid\Column|Collection client_no
     * @property Grid\Column|Collection sales_name
     * @property Grid\Column|Collection plan_list_no
     * @property Grid\Column|Collection client_order_no
     * @property Grid\Column|Collection product_time
     * @property Grid\Column|Collection spec_num
     * @property Grid\Column|Collection is_use
     * @property Grid\Column|Collection client_id
     * @property Grid\Column|Collection company_model_id
     * @property Grid\Column|Collection client_model_id
     * @property Grid\Column|Collection product_category_id
     * @property Grid\Column|Collection sole_material_id
     * @property Grid\Column|Collection craft_color_id
     * @property Grid\Column|Collection personnel_id
     * @property Grid\Column|Collection date_at
     * @property Grid\Column|Collection is_color
     * @property Grid\Column|Collection is_welt
     * @property Grid\Column|Collection is_copy
     * @property Grid\Column|Collection knife_mold
     * @property Grid\Column|Collection leather_piece
     * @property Grid\Column|Collection welt
     * @property Grid\Column|Collection sole
     * @property Grid\Column|Collection start_code
     * @property Grid\Column|Collection end_code
     * @property Grid\Column|Collection out
     * @property Grid\Column|Collection inject_mold_ask
     * @property Grid\Column|Collection craft_ask
     * @property Grid\Column|Collection after_storage_num
     * @property Grid\Column|Collection company_model_name
     * @property Grid\Column|Collection client_model_name
     * @property Grid\Column|Collection spec
     * @property Grid\Column|Collection type
     * @property Grid\Column|Collection in_num
     * @property Grid\Column|Collection storage
     * @property Grid\Column|Collection storage_in_date
     * @property Grid\Column|Collection dispatch_no
     * @property Grid\Column|Collection storage_type
     * @property Grid\Column|Collection style
     * @property Grid\Column|Collection raw_material_storage_out_no
     * @property Grid\Column|Collection apply_user_name
     * @property Grid\Column|Collection parent_id
     * @property Grid\Column|Collection order
     * @property Grid\Column|Collection icon
     * @property Grid\Column|Collection uri
     * @property Grid\Column|Collection img
     * @property Grid\Column|Collection user_id
     * @property Grid\Column|Collection permission_id
     * @property Grid\Column|Collection menu_id
     * @property Grid\Column|Collection http_method
     * @property Grid\Column|Collection http_path
     * @property Grid\Column|Collection role_id
     * @property Grid\Column|Collection password
     * @property Grid\Column|Collection remember_token
     * @property Grid\Column|Collection plan_list_id
     * @property Grid\Column|Collection dispatch_id
     * @property Grid\Column|Collection no
     * @property Grid\Column|Collection subject
     * @property Grid\Column|Collection check_at
     * @property Grid\Column|Collection void_at
     * @property Grid\Column|Collection deleted_at
     * @property Grid\Column|Collection dispatch_detail_id
     * @property Grid\Column|Collection box_label_dispatch_paper_id
     * @property Grid\Column|Collection num
     * @property Grid\Column|Collection client_category_id
     * @property Grid\Column|Collection sales_id
     * @property Grid\Column|Collection email
     * @property Grid\Column|Collection address
     * @property Grid\Column|Collection bank
     * @property Grid\Column|Collection bank_account
     * @property Grid\Column|Collection craft_information_id
     * @property Grid\Column|Collection sole_material_demand
     * @property Grid\Column|Collection carft_type_name
     * @property Grid\Column|Collection sole_image
     * @property Grid\Column|Collection old_company_model
     * @property Grid\Column|Collection old_client_model
     * @property Grid\Column|Collection delivery_no
     * @property Grid\Column|Collection content
     * @property Grid\Column|Collection all_num
     * @property Grid\Column|Collection delivery_price_id
     * @property Grid\Column|Collection delivery_price
     * @property Grid\Column|Collection log_user_id
     * @property Grid\Column|Collection delivery_at
     * @property Grid\Column|Collection delivery_user_id
     * @property Grid\Column|Collection delivery_user_name
     * @property Grid\Column|Collection delivery_type
     * @property Grid\Column|Collection is_print
     * @property Grid\Column|Collection delivery_id
     * @property Grid\Column|Collection plan_list_detail_id
     * @property Grid\Column|Collection delivery_category
     * @property Grid\Column|Collection delivery_date
     * @property Grid\Column|Collection delivery_log_id
     * @property Grid\Column|Collection total_num
     * @property Grid\Column|Collection total_price
     * @property Grid\Column|Collection delivery_detail_id
     * @property Grid\Column|Collection delivery_paper_id
     * @property Grid\Column|Collection is_price_delete
     * @property Grid\Column|Collection price_status
     * @property Grid\Column|Collection price_at
     * @property Grid\Column|Collection storage_in
     * @property Grid\Column|Collection storage_out
     * @property Grid\Column|Collection spec_id
     * @property Grid\Column|Collection storage_in_status
     * @property Grid\Column|Collection carft_skill_id
     * @property Grid\Column|Collection process_workshop
     * @property Grid\Column|Collection process_department
     * @property Grid\Column|Collection plan_remark
     * @property Grid\Column|Collection dispatch_user_id
     * @property Grid\Column|Collection dispatch_user_name
     * @property Grid\Column|Collection storage_out_status
     * @property Grid\Column|Collection connection
     * @property Grid\Column|Collection queue
     * @property Grid\Column|Collection payload
     * @property Grid\Column|Collection exception
     * @property Grid\Column|Collection failed_at
     * @property Grid\Column|Collection inject_mold_dispatch_paper_id
     * @property Grid\Column|Collection check_user_id
     * @property Grid\Column|Collection image
     * @property Grid\Column|Collection mold_maker_id
     * @property Grid\Column|Collection mold_category_child_id
     * @property Grid\Column|Collection mold_category_parent_id
     * @property Grid\Column|Collection money_from
     * @property Grid\Column|Collection sole_count
     * @property Grid\Column|Collection mold_information_id
     * @property Grid\Column|Collection old_is_void
     * @property Grid\Column|Collection new_is_void
     * @property Grid\Column|Collection void_user_id
     * @property Grid\Column|Collection void_user_name
     * @property Grid\Column|Collection token
     * @property Grid\Column|Collection department_id
     * @property Grid\Column|Collection position_id
     * @property Grid\Column|Collection birthday_at
     * @property Grid\Column|Collection idcard
     * @property Grid\Column|Collection come_at
     * @property Grid\Column|Collection work_at
     * @property Grid\Column|Collection out_at
     * @property Grid\Column|Collection nation
     * @property Grid\Column|Collection plan_category_name
     * @property Grid\Column|Collection client_sole_information_id
     * @property Grid\Column|Collection plan_category_id
     * @property Grid\Column|Collection plan_describe
     * @property Grid\Column|Collection process
     * @property Grid\Column|Collection sole_status
     * @property Grid\Column|Collection inject_mold_status
     * @property Grid\Column|Collection box_label_status
     * @property Grid\Column|Collection from
     * @property Grid\Column|Collection delivery_num
     * @property Grid\Column|Collection storage_out_num
     * @property Grid\Column|Collection delivery_status
     * @property Grid\Column|Collection sole_dispatch_num
     * @property Grid\Column|Collection inject_mold_dispatch_num
     * @property Grid\Column|Collection box_label_dispatch_num
     * @property Grid\Column|Collection sole_dispatch_complete
     * @property Grid\Column|Collection box_label_dispatch_complete
     * @property Grid\Column|Collection delivery_complete
     * @property Grid\Column|Collection raw_material_category_id
     * @property Grid\Column|Collection supplier_id
     * @property Grid\Column|Collection color_id
     * @property Grid\Column|Collection unit_id
     * @property Grid\Column|Collection raw_material_product_information_id
     * @property Grid\Column|Collection purchase_standard_id
     * @property Grid\Column|Collection raw_material_storage_id
     * @property Grid\Column|Collection raw_material_storage_out_id
     * @property Grid\Column|Collection check_time
     * @property Grid\Column|Collection sole_dispatch_paper_id
     * @property Grid\Column|Collection sole_material_color_id
     * @property Grid\Column|Collection sole_workshop_subscribe_no
     * @property Grid\Column|Collection subscribe_remark
     * @property Grid\Column|Collection subscribe_content
     * @property Grid\Column|Collection apply_user_id
     * @property Grid\Column|Collection sole_workshop_subscribe_detail_id
     * @property Grid\Column|Collection old_check_status
     * @property Grid\Column|Collection new_check_status
     * @property Grid\Column|Collection check_reason
     * @property Grid\Column|Collection old_num
     * @property Grid\Column|Collection now_approval_num
     * @property Grid\Column|Collection sole_workshop_subscribe_id
     * @property Grid\Column|Collection purcahse_standard_id
     * @property Grid\Column|Collection approval_num
     * @property Grid\Column|Collection apply_num
     * @property Grid\Column|Collection reason
     * @property Grid\Column|Collection craft_skill_name
     * @property Grid\Column|Collection logger_id
     * @property Grid\Column|Collection logger_name
     * @property Grid\Column|Collection count_type
     * @property Grid\Column|Collection inject_mold_price_id
     * @property Grid\Column|Collection inject_mold_price
     * @property Grid\Column|Collection transit_storage_id
     * @property Grid\Column|Collection out_date
     * @property Grid\Column|Collection trandit_storage_out_id
     * @property Grid\Column|Collection email_verified_at
     *
     * @method Grid\Column|Collection id(string $label = null)
     * @method Grid\Column|Collection username(string $label = null)
     * @method Grid\Column|Collection name(string $label = null)
     * @method Grid\Column|Collection roles(string $label = null)
     * @method Grid\Column|Collection permissions(string $label = null)
     * @method Grid\Column|Collection created_at(string $label = null)
     * @method Grid\Column|Collection updated_at(string $label = null)
     * @method Grid\Column|Collection avatar(string $label = null)
     * @method Grid\Column|Collection user(string $label = null)
     * @method Grid\Column|Collection method(string $label = null)
     * @method Grid\Column|Collection path(string $label = null)
     * @method Grid\Column|Collection ip(string $label = null)
     * @method Grid\Column|Collection input(string $label = null)
     * @method Grid\Column|Collection slug(string $label = null)
     * @method Grid\Column|Collection version(string $label = null)
     * @method Grid\Column|Collection alias(string $label = null)
     * @method Grid\Column|Collection authors(string $label = null)
     * @method Grid\Column|Collection enable(string $label = null)
     * @method Grid\Column|Collection imported(string $label = null)
     * @method Grid\Column|Collection config(string $label = null)
     * @method Grid\Column|Collection require(string $label = null)
     * @method Grid\Column|Collection require_dev(string $label = null)
     * @method Grid\Column|Collection personnel_no(string $label = null)
     * @method Grid\Column|Collection sex(string $label = null)
     * @method Grid\Column|Collection work_status(string $label = null)
     * @method Grid\Column|Collection department_name(string $label = null)
     * @method Grid\Column|Collection position_name(string $label = null)
     * @method Grid\Column|Collection mold_information_no(string $label = null)
     * @method Grid\Column|Collection client_name(string $label = null)
     * @method Grid\Column|Collection company_model(string $label = null)
     * @method Grid\Column|Collection mold_make_detail_standard(string $label = null)
     * @method Grid\Column|Collection actual_size(string $label = null)
     * @method Grid\Column|Collection settle_size(string $label = null)
     * @method Grid\Column|Collection price(string $label = null)
     * @method Grid\Column|Collection properties(string $label = null)
     * @method Grid\Column|Collection personnel_name(string $label = null)
     * @method Grid\Column|Collection mold_maker_name(string $label = null)
     * @method Grid\Column|Collection mold_category_parent_name(string $label = null)
     * @method Grid\Column|Collection mold_category_child_name(string $label = null)
     * @method Grid\Column|Collection log_user_name(string $label = null)
     * @method Grid\Column|Collection check(string $label = null)
     * @method Grid\Column|Collection status(string $label = null)
     * @method Grid\Column|Collection mold_maker_no(string $label = null)
     * @method Grid\Column|Collection pinyin(string $label = null)
     * @method Grid\Column|Collection add_at(string $label = null)
     * @method Grid\Column|Collection mold_category_name(string $label = null)
     * @method Grid\Column|Collection raw_material_category_name(string $label = null)
     * @method Grid\Column|Collection unit_name(string $label = null)
     * @method Grid\Column|Collection supplier_no(string $label = null)
     * @method Grid\Column|Collection supplier_name(string $label = null)
     * @method Grid\Column|Collection contact(string $label = null)
     * @method Grid\Column|Collection tel(string $label = null)
     * @method Grid\Column|Collection fax(string $label = null)
     * @method Grid\Column|Collection purchase_standard_name(string $label = null)
     * @method Grid\Column|Collection color_name(string $label = null)
     * @method Grid\Column|Collection raw_material_product_information_no(string $label = null)
     * @method Grid\Column|Collection raw_material_product_information_name(string $label = null)
     * @method Grid\Column|Collection unit(string $label = null)
     * @method Grid\Column|Collection material_level(string $label = null)
     * @method Grid\Column|Collection color(string $label = null)
     * @method Grid\Column|Collection standard(string $label = null)
     * @method Grid\Column|Collection change_coefficient(string $label = null)
     * @method Grid\Column|Collection mold_type(string $label = null)
     * @method Grid\Column|Collection out_num(string $label = null)
     * @method Grid\Column|Collection product_feature(string $label = null)
     * @method Grid\Column|Collection product_category_name(string $label = null)
     * @method Grid\Column|Collection remark(string $label = null)
     * @method Grid\Column|Collection check_user_name(string $label = null)
     * @method Grid\Column|Collection purcahse_standard_name(string $label = null)
     * @method Grid\Column|Collection storage_in_num(string $label = null)
     * @method Grid\Column|Collection check_status(string $label = null)
     * @method Grid\Column|Collection is_void(string $label = null)
     * @method Grid\Column|Collection is_check(string $label = null)
     * @method Grid\Column|Collection void_reason(string $label = null)
     * @method Grid\Column|Collection sole_material_color_name(string $label = null)
     * @method Grid\Column|Collection sole_material_name(string $label = null)
     * @method Grid\Column|Collection carft_skill_name(string $label = null)
     * @method Grid\Column|Collection client_model(string $label = null)
     * @method Grid\Column|Collection craft_color_name(string $label = null)
     * @method Grid\Column|Collection standard_detail_name(string $label = null)
     * @method Grid\Column|Collection client_no(string $label = null)
     * @method Grid\Column|Collection sales_name(string $label = null)
     * @method Grid\Column|Collection plan_list_no(string $label = null)
     * @method Grid\Column|Collection client_order_no(string $label = null)
     * @method Grid\Column|Collection product_time(string $label = null)
     * @method Grid\Column|Collection spec_num(string $label = null)
     * @method Grid\Column|Collection is_use(string $label = null)
     * @method Grid\Column|Collection client_id(string $label = null)
     * @method Grid\Column|Collection company_model_id(string $label = null)
     * @method Grid\Column|Collection client_model_id(string $label = null)
     * @method Grid\Column|Collection product_category_id(string $label = null)
     * @method Grid\Column|Collection sole_material_id(string $label = null)
     * @method Grid\Column|Collection craft_color_id(string $label = null)
     * @method Grid\Column|Collection personnel_id(string $label = null)
     * @method Grid\Column|Collection date_at(string $label = null)
     * @method Grid\Column|Collection is_color(string $label = null)
     * @method Grid\Column|Collection is_welt(string $label = null)
     * @method Grid\Column|Collection is_copy(string $label = null)
     * @method Grid\Column|Collection knife_mold(string $label = null)
     * @method Grid\Column|Collection leather_piece(string $label = null)
     * @method Grid\Column|Collection welt(string $label = null)
     * @method Grid\Column|Collection sole(string $label = null)
     * @method Grid\Column|Collection start_code(string $label = null)
     * @method Grid\Column|Collection end_code(string $label = null)
     * @method Grid\Column|Collection out(string $label = null)
     * @method Grid\Column|Collection inject_mold_ask(string $label = null)
     * @method Grid\Column|Collection craft_ask(string $label = null)
     * @method Grid\Column|Collection after_storage_num(string $label = null)
     * @method Grid\Column|Collection company_model_name(string $label = null)
     * @method Grid\Column|Collection client_model_name(string $label = null)
     * @method Grid\Column|Collection spec(string $label = null)
     * @method Grid\Column|Collection type(string $label = null)
     * @method Grid\Column|Collection in_num(string $label = null)
     * @method Grid\Column|Collection storage(string $label = null)
     * @method Grid\Column|Collection storage_in_date(string $label = null)
     * @method Grid\Column|Collection dispatch_no(string $label = null)
     * @method Grid\Column|Collection storage_type(string $label = null)
     * @method Grid\Column|Collection style(string $label = null)
     * @method Grid\Column|Collection raw_material_storage_out_no(string $label = null)
     * @method Grid\Column|Collection apply_user_name(string $label = null)
     * @method Grid\Column|Collection parent_id(string $label = null)
     * @method Grid\Column|Collection order(string $label = null)
     * @method Grid\Column|Collection icon(string $label = null)
     * @method Grid\Column|Collection uri(string $label = null)
     * @method Grid\Column|Collection img(string $label = null)
     * @method Grid\Column|Collection user_id(string $label = null)
     * @method Grid\Column|Collection permission_id(string $label = null)
     * @method Grid\Column|Collection menu_id(string $label = null)
     * @method Grid\Column|Collection http_method(string $label = null)
     * @method Grid\Column|Collection http_path(string $label = null)
     * @method Grid\Column|Collection role_id(string $label = null)
     * @method Grid\Column|Collection password(string $label = null)
     * @method Grid\Column|Collection remember_token(string $label = null)
     * @method Grid\Column|Collection plan_list_id(string $label = null)
     * @method Grid\Column|Collection dispatch_id(string $label = null)
     * @method Grid\Column|Collection no(string $label = null)
     * @method Grid\Column|Collection subject(string $label = null)
     * @method Grid\Column|Collection check_at(string $label = null)
     * @method Grid\Column|Collection void_at(string $label = null)
     * @method Grid\Column|Collection deleted_at(string $label = null)
     * @method Grid\Column|Collection dispatch_detail_id(string $label = null)
     * @method Grid\Column|Collection box_label_dispatch_paper_id(string $label = null)
     * @method Grid\Column|Collection num(string $label = null)
     * @method Grid\Column|Collection client_category_id(string $label = null)
     * @method Grid\Column|Collection sales_id(string $label = null)
     * @method Grid\Column|Collection email(string $label = null)
     * @method Grid\Column|Collection address(string $label = null)
     * @method Grid\Column|Collection bank(string $label = null)
     * @method Grid\Column|Collection bank_account(string $label = null)
     * @method Grid\Column|Collection craft_information_id(string $label = null)
     * @method Grid\Column|Collection sole_material_demand(string $label = null)
     * @method Grid\Column|Collection carft_type_name(string $label = null)
     * @method Grid\Column|Collection sole_image(string $label = null)
     * @method Grid\Column|Collection old_company_model(string $label = null)
     * @method Grid\Column|Collection old_client_model(string $label = null)
     * @method Grid\Column|Collection delivery_no(string $label = null)
     * @method Grid\Column|Collection content(string $label = null)
     * @method Grid\Column|Collection all_num(string $label = null)
     * @method Grid\Column|Collection delivery_price_id(string $label = null)
     * @method Grid\Column|Collection delivery_price(string $label = null)
     * @method Grid\Column|Collection log_user_id(string $label = null)
     * @method Grid\Column|Collection delivery_at(string $label = null)
     * @method Grid\Column|Collection delivery_user_id(string $label = null)
     * @method Grid\Column|Collection delivery_user_name(string $label = null)
     * @method Grid\Column|Collection delivery_type(string $label = null)
     * @method Grid\Column|Collection is_print(string $label = null)
     * @method Grid\Column|Collection delivery_id(string $label = null)
     * @method Grid\Column|Collection plan_list_detail_id(string $label = null)
     * @method Grid\Column|Collection delivery_category(string $label = null)
     * @method Grid\Column|Collection delivery_date(string $label = null)
     * @method Grid\Column|Collection delivery_log_id(string $label = null)
     * @method Grid\Column|Collection total_num(string $label = null)
     * @method Grid\Column|Collection total_price(string $label = null)
     * @method Grid\Column|Collection delivery_detail_id(string $label = null)
     * @method Grid\Column|Collection delivery_paper_id(string $label = null)
     * @method Grid\Column|Collection is_price_delete(string $label = null)
     * @method Grid\Column|Collection price_status(string $label = null)
     * @method Grid\Column|Collection price_at(string $label = null)
     * @method Grid\Column|Collection storage_in(string $label = null)
     * @method Grid\Column|Collection storage_out(string $label = null)
     * @method Grid\Column|Collection spec_id(string $label = null)
     * @method Grid\Column|Collection storage_in_status(string $label = null)
     * @method Grid\Column|Collection carft_skill_id(string $label = null)
     * @method Grid\Column|Collection process_workshop(string $label = null)
     * @method Grid\Column|Collection process_department(string $label = null)
     * @method Grid\Column|Collection plan_remark(string $label = null)
     * @method Grid\Column|Collection dispatch_user_id(string $label = null)
     * @method Grid\Column|Collection dispatch_user_name(string $label = null)
     * @method Grid\Column|Collection storage_out_status(string $label = null)
     * @method Grid\Column|Collection connection(string $label = null)
     * @method Grid\Column|Collection queue(string $label = null)
     * @method Grid\Column|Collection payload(string $label = null)
     * @method Grid\Column|Collection exception(string $label = null)
     * @method Grid\Column|Collection failed_at(string $label = null)
     * @method Grid\Column|Collection inject_mold_dispatch_paper_id(string $label = null)
     * @method Grid\Column|Collection check_user_id(string $label = null)
     * @method Grid\Column|Collection image(string $label = null)
     * @method Grid\Column|Collection mold_maker_id(string $label = null)
     * @method Grid\Column|Collection mold_category_child_id(string $label = null)
     * @method Grid\Column|Collection mold_category_parent_id(string $label = null)
     * @method Grid\Column|Collection money_from(string $label = null)
     * @method Grid\Column|Collection sole_count(string $label = null)
     * @method Grid\Column|Collection mold_information_id(string $label = null)
     * @method Grid\Column|Collection old_is_void(string $label = null)
     * @method Grid\Column|Collection new_is_void(string $label = null)
     * @method Grid\Column|Collection void_user_id(string $label = null)
     * @method Grid\Column|Collection void_user_name(string $label = null)
     * @method Grid\Column|Collection token(string $label = null)
     * @method Grid\Column|Collection department_id(string $label = null)
     * @method Grid\Column|Collection position_id(string $label = null)
     * @method Grid\Column|Collection birthday_at(string $label = null)
     * @method Grid\Column|Collection idcard(string $label = null)
     * @method Grid\Column|Collection come_at(string $label = null)
     * @method Grid\Column|Collection work_at(string $label = null)
     * @method Grid\Column|Collection out_at(string $label = null)
     * @method Grid\Column|Collection nation(string $label = null)
     * @method Grid\Column|Collection plan_category_name(string $label = null)
     * @method Grid\Column|Collection client_sole_information_id(string $label = null)
     * @method Grid\Column|Collection plan_category_id(string $label = null)
     * @method Grid\Column|Collection plan_describe(string $label = null)
     * @method Grid\Column|Collection process(string $label = null)
     * @method Grid\Column|Collection sole_status(string $label = null)
     * @method Grid\Column|Collection inject_mold_status(string $label = null)
     * @method Grid\Column|Collection box_label_status(string $label = null)
     * @method Grid\Column|Collection from(string $label = null)
     * @method Grid\Column|Collection delivery_num(string $label = null)
     * @method Grid\Column|Collection storage_out_num(string $label = null)
     * @method Grid\Column|Collection delivery_status(string $label = null)
     * @method Grid\Column|Collection sole_dispatch_num(string $label = null)
     * @method Grid\Column|Collection inject_mold_dispatch_num(string $label = null)
     * @method Grid\Column|Collection box_label_dispatch_num(string $label = null)
     * @method Grid\Column|Collection sole_dispatch_complete(string $label = null)
     * @method Grid\Column|Collection box_label_dispatch_complete(string $label = null)
     * @method Grid\Column|Collection delivery_complete(string $label = null)
     * @method Grid\Column|Collection raw_material_category_id(string $label = null)
     * @method Grid\Column|Collection supplier_id(string $label = null)
     * @method Grid\Column|Collection color_id(string $label = null)
     * @method Grid\Column|Collection unit_id(string $label = null)
     * @method Grid\Column|Collection raw_material_product_information_id(string $label = null)
     * @method Grid\Column|Collection purchase_standard_id(string $label = null)
     * @method Grid\Column|Collection raw_material_storage_id(string $label = null)
     * @method Grid\Column|Collection raw_material_storage_out_id(string $label = null)
     * @method Grid\Column|Collection check_time(string $label = null)
     * @method Grid\Column|Collection sole_dispatch_paper_id(string $label = null)
     * @method Grid\Column|Collection sole_material_color_id(string $label = null)
     * @method Grid\Column|Collection sole_workshop_subscribe_no(string $label = null)
     * @method Grid\Column|Collection subscribe_remark(string $label = null)
     * @method Grid\Column|Collection subscribe_content(string $label = null)
     * @method Grid\Column|Collection apply_user_id(string $label = null)
     * @method Grid\Column|Collection sole_workshop_subscribe_detail_id(string $label = null)
     * @method Grid\Column|Collection old_check_status(string $label = null)
     * @method Grid\Column|Collection new_check_status(string $label = null)
     * @method Grid\Column|Collection check_reason(string $label = null)
     * @method Grid\Column|Collection old_num(string $label = null)
     * @method Grid\Column|Collection now_approval_num(string $label = null)
     * @method Grid\Column|Collection sole_workshop_subscribe_id(string $label = null)
     * @method Grid\Column|Collection purcahse_standard_id(string $label = null)
     * @method Grid\Column|Collection approval_num(string $label = null)
     * @method Grid\Column|Collection apply_num(string $label = null)
     * @method Grid\Column|Collection reason(string $label = null)
     * @method Grid\Column|Collection craft_skill_name(string $label = null)
     * @method Grid\Column|Collection logger_id(string $label = null)
     * @method Grid\Column|Collection logger_name(string $label = null)
     * @method Grid\Column|Collection count_type(string $label = null)
     * @method Grid\Column|Collection inject_mold_price_id(string $label = null)
     * @method Grid\Column|Collection inject_mold_price(string $label = null)
     * @method Grid\Column|Collection transit_storage_id(string $label = null)
     * @method Grid\Column|Collection out_date(string $label = null)
     * @method Grid\Column|Collection trandit_storage_out_id(string $label = null)
     * @method Grid\Column|Collection email_verified_at(string $label = null)
     */
    class Grid {}

    class MiniGrid extends Grid {}

    /**
     * @property Show\Field|Collection id
     * @property Show\Field|Collection username
     * @property Show\Field|Collection name
     * @property Show\Field|Collection roles
     * @property Show\Field|Collection permissions
     * @property Show\Field|Collection created_at
     * @property Show\Field|Collection updated_at
     * @property Show\Field|Collection avatar
     * @property Show\Field|Collection user
     * @property Show\Field|Collection method
     * @property Show\Field|Collection path
     * @property Show\Field|Collection ip
     * @property Show\Field|Collection input
     * @property Show\Field|Collection slug
     * @property Show\Field|Collection version
     * @property Show\Field|Collection alias
     * @property Show\Field|Collection authors
     * @property Show\Field|Collection enable
     * @property Show\Field|Collection imported
     * @property Show\Field|Collection config
     * @property Show\Field|Collection require
     * @property Show\Field|Collection require_dev
     * @property Show\Field|Collection personnel_no
     * @property Show\Field|Collection sex
     * @property Show\Field|Collection work_status
     * @property Show\Field|Collection department_name
     * @property Show\Field|Collection position_name
     * @property Show\Field|Collection mold_information_no
     * @property Show\Field|Collection client_name
     * @property Show\Field|Collection company_model
     * @property Show\Field|Collection mold_make_detail_standard
     * @property Show\Field|Collection actual_size
     * @property Show\Field|Collection settle_size
     * @property Show\Field|Collection price
     * @property Show\Field|Collection properties
     * @property Show\Field|Collection personnel_name
     * @property Show\Field|Collection mold_maker_name
     * @property Show\Field|Collection mold_category_parent_name
     * @property Show\Field|Collection mold_category_child_name
     * @property Show\Field|Collection log_user_name
     * @property Show\Field|Collection check
     * @property Show\Field|Collection status
     * @property Show\Field|Collection mold_maker_no
     * @property Show\Field|Collection pinyin
     * @property Show\Field|Collection add_at
     * @property Show\Field|Collection mold_category_name
     * @property Show\Field|Collection raw_material_category_name
     * @property Show\Field|Collection unit_name
     * @property Show\Field|Collection supplier_no
     * @property Show\Field|Collection supplier_name
     * @property Show\Field|Collection contact
     * @property Show\Field|Collection tel
     * @property Show\Field|Collection fax
     * @property Show\Field|Collection purchase_standard_name
     * @property Show\Field|Collection color_name
     * @property Show\Field|Collection raw_material_product_information_no
     * @property Show\Field|Collection raw_material_product_information_name
     * @property Show\Field|Collection unit
     * @property Show\Field|Collection material_level
     * @property Show\Field|Collection color
     * @property Show\Field|Collection standard
     * @property Show\Field|Collection change_coefficient
     * @property Show\Field|Collection mold_type
     * @property Show\Field|Collection out_num
     * @property Show\Field|Collection product_feature
     * @property Show\Field|Collection product_category_name
     * @property Show\Field|Collection remark
     * @property Show\Field|Collection check_user_name
     * @property Show\Field|Collection purcahse_standard_name
     * @property Show\Field|Collection storage_in_num
     * @property Show\Field|Collection check_status
     * @property Show\Field|Collection is_void
     * @property Show\Field|Collection is_check
     * @property Show\Field|Collection void_reason
     * @property Show\Field|Collection sole_material_color_name
     * @property Show\Field|Collection sole_material_name
     * @property Show\Field|Collection carft_skill_name
     * @property Show\Field|Collection client_model
     * @property Show\Field|Collection craft_color_name
     * @property Show\Field|Collection standard_detail_name
     * @property Show\Field|Collection client_no
     * @property Show\Field|Collection sales_name
     * @property Show\Field|Collection plan_list_no
     * @property Show\Field|Collection client_order_no
     * @property Show\Field|Collection product_time
     * @property Show\Field|Collection spec_num
     * @property Show\Field|Collection is_use
     * @property Show\Field|Collection client_id
     * @property Show\Field|Collection company_model_id
     * @property Show\Field|Collection client_model_id
     * @property Show\Field|Collection product_category_id
     * @property Show\Field|Collection sole_material_id
     * @property Show\Field|Collection craft_color_id
     * @property Show\Field|Collection personnel_id
     * @property Show\Field|Collection date_at
     * @property Show\Field|Collection is_color
     * @property Show\Field|Collection is_welt
     * @property Show\Field|Collection is_copy
     * @property Show\Field|Collection knife_mold
     * @property Show\Field|Collection leather_piece
     * @property Show\Field|Collection welt
     * @property Show\Field|Collection sole
     * @property Show\Field|Collection start_code
     * @property Show\Field|Collection end_code
     * @property Show\Field|Collection out
     * @property Show\Field|Collection inject_mold_ask
     * @property Show\Field|Collection craft_ask
     * @property Show\Field|Collection after_storage_num
     * @property Show\Field|Collection company_model_name
     * @property Show\Field|Collection client_model_name
     * @property Show\Field|Collection spec
     * @property Show\Field|Collection type
     * @property Show\Field|Collection in_num
     * @property Show\Field|Collection storage
     * @property Show\Field|Collection storage_in_date
     * @property Show\Field|Collection dispatch_no
     * @property Show\Field|Collection storage_type
     * @property Show\Field|Collection style
     * @property Show\Field|Collection raw_material_storage_out_no
     * @property Show\Field|Collection apply_user_name
     * @property Show\Field|Collection parent_id
     * @property Show\Field|Collection order
     * @property Show\Field|Collection icon
     * @property Show\Field|Collection uri
     * @property Show\Field|Collection img
     * @property Show\Field|Collection user_id
     * @property Show\Field|Collection permission_id
     * @property Show\Field|Collection menu_id
     * @property Show\Field|Collection http_method
     * @property Show\Field|Collection http_path
     * @property Show\Field|Collection role_id
     * @property Show\Field|Collection password
     * @property Show\Field|Collection remember_token
     * @property Show\Field|Collection plan_list_id
     * @property Show\Field|Collection dispatch_id
     * @property Show\Field|Collection no
     * @property Show\Field|Collection subject
     * @property Show\Field|Collection check_at
     * @property Show\Field|Collection void_at
     * @property Show\Field|Collection deleted_at
     * @property Show\Field|Collection dispatch_detail_id
     * @property Show\Field|Collection box_label_dispatch_paper_id
     * @property Show\Field|Collection num
     * @property Show\Field|Collection client_category_id
     * @property Show\Field|Collection sales_id
     * @property Show\Field|Collection email
     * @property Show\Field|Collection address
     * @property Show\Field|Collection bank
     * @property Show\Field|Collection bank_account
     * @property Show\Field|Collection craft_information_id
     * @property Show\Field|Collection sole_material_demand
     * @property Show\Field|Collection carft_type_name
     * @property Show\Field|Collection sole_image
     * @property Show\Field|Collection old_company_model
     * @property Show\Field|Collection old_client_model
     * @property Show\Field|Collection delivery_no
     * @property Show\Field|Collection content
     * @property Show\Field|Collection all_num
     * @property Show\Field|Collection delivery_price_id
     * @property Show\Field|Collection delivery_price
     * @property Show\Field|Collection log_user_id
     * @property Show\Field|Collection delivery_at
     * @property Show\Field|Collection delivery_user_id
     * @property Show\Field|Collection delivery_user_name
     * @property Show\Field|Collection delivery_type
     * @property Show\Field|Collection is_print
     * @property Show\Field|Collection delivery_id
     * @property Show\Field|Collection plan_list_detail_id
     * @property Show\Field|Collection delivery_category
     * @property Show\Field|Collection delivery_date
     * @property Show\Field|Collection delivery_log_id
     * @property Show\Field|Collection total_num
     * @property Show\Field|Collection total_price
     * @property Show\Field|Collection delivery_detail_id
     * @property Show\Field|Collection delivery_paper_id
     * @property Show\Field|Collection is_price_delete
     * @property Show\Field|Collection price_status
     * @property Show\Field|Collection price_at
     * @property Show\Field|Collection storage_in
     * @property Show\Field|Collection storage_out
     * @property Show\Field|Collection spec_id
     * @property Show\Field|Collection storage_in_status
     * @property Show\Field|Collection carft_skill_id
     * @property Show\Field|Collection process_workshop
     * @property Show\Field|Collection process_department
     * @property Show\Field|Collection plan_remark
     * @property Show\Field|Collection dispatch_user_id
     * @property Show\Field|Collection dispatch_user_name
     * @property Show\Field|Collection storage_out_status
     * @property Show\Field|Collection connection
     * @property Show\Field|Collection queue
     * @property Show\Field|Collection payload
     * @property Show\Field|Collection exception
     * @property Show\Field|Collection failed_at
     * @property Show\Field|Collection inject_mold_dispatch_paper_id
     * @property Show\Field|Collection check_user_id
     * @property Show\Field|Collection image
     * @property Show\Field|Collection mold_maker_id
     * @property Show\Field|Collection mold_category_child_id
     * @property Show\Field|Collection mold_category_parent_id
     * @property Show\Field|Collection money_from
     * @property Show\Field|Collection sole_count
     * @property Show\Field|Collection mold_information_id
     * @property Show\Field|Collection old_is_void
     * @property Show\Field|Collection new_is_void
     * @property Show\Field|Collection void_user_id
     * @property Show\Field|Collection void_user_name
     * @property Show\Field|Collection token
     * @property Show\Field|Collection department_id
     * @property Show\Field|Collection position_id
     * @property Show\Field|Collection birthday_at
     * @property Show\Field|Collection idcard
     * @property Show\Field|Collection come_at
     * @property Show\Field|Collection work_at
     * @property Show\Field|Collection out_at
     * @property Show\Field|Collection nation
     * @property Show\Field|Collection plan_category_name
     * @property Show\Field|Collection client_sole_information_id
     * @property Show\Field|Collection plan_category_id
     * @property Show\Field|Collection plan_describe
     * @property Show\Field|Collection process
     * @property Show\Field|Collection sole_status
     * @property Show\Field|Collection inject_mold_status
     * @property Show\Field|Collection box_label_status
     * @property Show\Field|Collection from
     * @property Show\Field|Collection delivery_num
     * @property Show\Field|Collection storage_out_num
     * @property Show\Field|Collection delivery_status
     * @property Show\Field|Collection sole_dispatch_num
     * @property Show\Field|Collection inject_mold_dispatch_num
     * @property Show\Field|Collection box_label_dispatch_num
     * @property Show\Field|Collection sole_dispatch_complete
     * @property Show\Field|Collection box_label_dispatch_complete
     * @property Show\Field|Collection delivery_complete
     * @property Show\Field|Collection raw_material_category_id
     * @property Show\Field|Collection supplier_id
     * @property Show\Field|Collection color_id
     * @property Show\Field|Collection unit_id
     * @property Show\Field|Collection raw_material_product_information_id
     * @property Show\Field|Collection purchase_standard_id
     * @property Show\Field|Collection raw_material_storage_id
     * @property Show\Field|Collection raw_material_storage_out_id
     * @property Show\Field|Collection check_time
     * @property Show\Field|Collection sole_dispatch_paper_id
     * @property Show\Field|Collection sole_material_color_id
     * @property Show\Field|Collection sole_workshop_subscribe_no
     * @property Show\Field|Collection subscribe_remark
     * @property Show\Field|Collection subscribe_content
     * @property Show\Field|Collection apply_user_id
     * @property Show\Field|Collection sole_workshop_subscribe_detail_id
     * @property Show\Field|Collection old_check_status
     * @property Show\Field|Collection new_check_status
     * @property Show\Field|Collection check_reason
     * @property Show\Field|Collection old_num
     * @property Show\Field|Collection now_approval_num
     * @property Show\Field|Collection sole_workshop_subscribe_id
     * @property Show\Field|Collection purcahse_standard_id
     * @property Show\Field|Collection approval_num
     * @property Show\Field|Collection apply_num
     * @property Show\Field|Collection reason
     * @property Show\Field|Collection craft_skill_name
     * @property Show\Field|Collection logger_id
     * @property Show\Field|Collection logger_name
     * @property Show\Field|Collection count_type
     * @property Show\Field|Collection inject_mold_price_id
     * @property Show\Field|Collection inject_mold_price
     * @property Show\Field|Collection transit_storage_id
     * @property Show\Field|Collection out_date
     * @property Show\Field|Collection trandit_storage_out_id
     * @property Show\Field|Collection email_verified_at
     *
     * @method Show\Field|Collection id(string $label = null)
     * @method Show\Field|Collection username(string $label = null)
     * @method Show\Field|Collection name(string $label = null)
     * @method Show\Field|Collection roles(string $label = null)
     * @method Show\Field|Collection permissions(string $label = null)
     * @method Show\Field|Collection created_at(string $label = null)
     * @method Show\Field|Collection updated_at(string $label = null)
     * @method Show\Field|Collection avatar(string $label = null)
     * @method Show\Field|Collection user(string $label = null)
     * @method Show\Field|Collection method(string $label = null)
     * @method Show\Field|Collection path(string $label = null)
     * @method Show\Field|Collection ip(string $label = null)
     * @method Show\Field|Collection input(string $label = null)
     * @method Show\Field|Collection slug(string $label = null)
     * @method Show\Field|Collection version(string $label = null)
     * @method Show\Field|Collection alias(string $label = null)
     * @method Show\Field|Collection authors(string $label = null)
     * @method Show\Field|Collection enable(string $label = null)
     * @method Show\Field|Collection imported(string $label = null)
     * @method Show\Field|Collection config(string $label = null)
     * @method Show\Field|Collection require(string $label = null)
     * @method Show\Field|Collection require_dev(string $label = null)
     * @method Show\Field|Collection personnel_no(string $label = null)
     * @method Show\Field|Collection sex(string $label = null)
     * @method Show\Field|Collection work_status(string $label = null)
     * @method Show\Field|Collection department_name(string $label = null)
     * @method Show\Field|Collection position_name(string $label = null)
     * @method Show\Field|Collection mold_information_no(string $label = null)
     * @method Show\Field|Collection client_name(string $label = null)
     * @method Show\Field|Collection company_model(string $label = null)
     * @method Show\Field|Collection mold_make_detail_standard(string $label = null)
     * @method Show\Field|Collection actual_size(string $label = null)
     * @method Show\Field|Collection settle_size(string $label = null)
     * @method Show\Field|Collection price(string $label = null)
     * @method Show\Field|Collection properties(string $label = null)
     * @method Show\Field|Collection personnel_name(string $label = null)
     * @method Show\Field|Collection mold_maker_name(string $label = null)
     * @method Show\Field|Collection mold_category_parent_name(string $label = null)
     * @method Show\Field|Collection mold_category_child_name(string $label = null)
     * @method Show\Field|Collection log_user_name(string $label = null)
     * @method Show\Field|Collection check(string $label = null)
     * @method Show\Field|Collection status(string $label = null)
     * @method Show\Field|Collection mold_maker_no(string $label = null)
     * @method Show\Field|Collection pinyin(string $label = null)
     * @method Show\Field|Collection add_at(string $label = null)
     * @method Show\Field|Collection mold_category_name(string $label = null)
     * @method Show\Field|Collection raw_material_category_name(string $label = null)
     * @method Show\Field|Collection unit_name(string $label = null)
     * @method Show\Field|Collection supplier_no(string $label = null)
     * @method Show\Field|Collection supplier_name(string $label = null)
     * @method Show\Field|Collection contact(string $label = null)
     * @method Show\Field|Collection tel(string $label = null)
     * @method Show\Field|Collection fax(string $label = null)
     * @method Show\Field|Collection purchase_standard_name(string $label = null)
     * @method Show\Field|Collection color_name(string $label = null)
     * @method Show\Field|Collection raw_material_product_information_no(string $label = null)
     * @method Show\Field|Collection raw_material_product_information_name(string $label = null)
     * @method Show\Field|Collection unit(string $label = null)
     * @method Show\Field|Collection material_level(string $label = null)
     * @method Show\Field|Collection color(string $label = null)
     * @method Show\Field|Collection standard(string $label = null)
     * @method Show\Field|Collection change_coefficient(string $label = null)
     * @method Show\Field|Collection mold_type(string $label = null)
     * @method Show\Field|Collection out_num(string $label = null)
     * @method Show\Field|Collection product_feature(string $label = null)
     * @method Show\Field|Collection product_category_name(string $label = null)
     * @method Show\Field|Collection remark(string $label = null)
     * @method Show\Field|Collection check_user_name(string $label = null)
     * @method Show\Field|Collection purcahse_standard_name(string $label = null)
     * @method Show\Field|Collection storage_in_num(string $label = null)
     * @method Show\Field|Collection check_status(string $label = null)
     * @method Show\Field|Collection is_void(string $label = null)
     * @method Show\Field|Collection is_check(string $label = null)
     * @method Show\Field|Collection void_reason(string $label = null)
     * @method Show\Field|Collection sole_material_color_name(string $label = null)
     * @method Show\Field|Collection sole_material_name(string $label = null)
     * @method Show\Field|Collection carft_skill_name(string $label = null)
     * @method Show\Field|Collection client_model(string $label = null)
     * @method Show\Field|Collection craft_color_name(string $label = null)
     * @method Show\Field|Collection standard_detail_name(string $label = null)
     * @method Show\Field|Collection client_no(string $label = null)
     * @method Show\Field|Collection sales_name(string $label = null)
     * @method Show\Field|Collection plan_list_no(string $label = null)
     * @method Show\Field|Collection client_order_no(string $label = null)
     * @method Show\Field|Collection product_time(string $label = null)
     * @method Show\Field|Collection spec_num(string $label = null)
     * @method Show\Field|Collection is_use(string $label = null)
     * @method Show\Field|Collection client_id(string $label = null)
     * @method Show\Field|Collection company_model_id(string $label = null)
     * @method Show\Field|Collection client_model_id(string $label = null)
     * @method Show\Field|Collection product_category_id(string $label = null)
     * @method Show\Field|Collection sole_material_id(string $label = null)
     * @method Show\Field|Collection craft_color_id(string $label = null)
     * @method Show\Field|Collection personnel_id(string $label = null)
     * @method Show\Field|Collection date_at(string $label = null)
     * @method Show\Field|Collection is_color(string $label = null)
     * @method Show\Field|Collection is_welt(string $label = null)
     * @method Show\Field|Collection is_copy(string $label = null)
     * @method Show\Field|Collection knife_mold(string $label = null)
     * @method Show\Field|Collection leather_piece(string $label = null)
     * @method Show\Field|Collection welt(string $label = null)
     * @method Show\Field|Collection sole(string $label = null)
     * @method Show\Field|Collection start_code(string $label = null)
     * @method Show\Field|Collection end_code(string $label = null)
     * @method Show\Field|Collection out(string $label = null)
     * @method Show\Field|Collection inject_mold_ask(string $label = null)
     * @method Show\Field|Collection craft_ask(string $label = null)
     * @method Show\Field|Collection after_storage_num(string $label = null)
     * @method Show\Field|Collection company_model_name(string $label = null)
     * @method Show\Field|Collection client_model_name(string $label = null)
     * @method Show\Field|Collection spec(string $label = null)
     * @method Show\Field|Collection type(string $label = null)
     * @method Show\Field|Collection in_num(string $label = null)
     * @method Show\Field|Collection storage(string $label = null)
     * @method Show\Field|Collection storage_in_date(string $label = null)
     * @method Show\Field|Collection dispatch_no(string $label = null)
     * @method Show\Field|Collection storage_type(string $label = null)
     * @method Show\Field|Collection style(string $label = null)
     * @method Show\Field|Collection raw_material_storage_out_no(string $label = null)
     * @method Show\Field|Collection apply_user_name(string $label = null)
     * @method Show\Field|Collection parent_id(string $label = null)
     * @method Show\Field|Collection order(string $label = null)
     * @method Show\Field|Collection icon(string $label = null)
     * @method Show\Field|Collection uri(string $label = null)
     * @method Show\Field|Collection img(string $label = null)
     * @method Show\Field|Collection user_id(string $label = null)
     * @method Show\Field|Collection permission_id(string $label = null)
     * @method Show\Field|Collection menu_id(string $label = null)
     * @method Show\Field|Collection http_method(string $label = null)
     * @method Show\Field|Collection http_path(string $label = null)
     * @method Show\Field|Collection role_id(string $label = null)
     * @method Show\Field|Collection password(string $label = null)
     * @method Show\Field|Collection remember_token(string $label = null)
     * @method Show\Field|Collection plan_list_id(string $label = null)
     * @method Show\Field|Collection dispatch_id(string $label = null)
     * @method Show\Field|Collection no(string $label = null)
     * @method Show\Field|Collection subject(string $label = null)
     * @method Show\Field|Collection check_at(string $label = null)
     * @method Show\Field|Collection void_at(string $label = null)
     * @method Show\Field|Collection deleted_at(string $label = null)
     * @method Show\Field|Collection dispatch_detail_id(string $label = null)
     * @method Show\Field|Collection box_label_dispatch_paper_id(string $label = null)
     * @method Show\Field|Collection num(string $label = null)
     * @method Show\Field|Collection client_category_id(string $label = null)
     * @method Show\Field|Collection sales_id(string $label = null)
     * @method Show\Field|Collection email(string $label = null)
     * @method Show\Field|Collection address(string $label = null)
     * @method Show\Field|Collection bank(string $label = null)
     * @method Show\Field|Collection bank_account(string $label = null)
     * @method Show\Field|Collection craft_information_id(string $label = null)
     * @method Show\Field|Collection sole_material_demand(string $label = null)
     * @method Show\Field|Collection carft_type_name(string $label = null)
     * @method Show\Field|Collection sole_image(string $label = null)
     * @method Show\Field|Collection old_company_model(string $label = null)
     * @method Show\Field|Collection old_client_model(string $label = null)
     * @method Show\Field|Collection delivery_no(string $label = null)
     * @method Show\Field|Collection content(string $label = null)
     * @method Show\Field|Collection all_num(string $label = null)
     * @method Show\Field|Collection delivery_price_id(string $label = null)
     * @method Show\Field|Collection delivery_price(string $label = null)
     * @method Show\Field|Collection log_user_id(string $label = null)
     * @method Show\Field|Collection delivery_at(string $label = null)
     * @method Show\Field|Collection delivery_user_id(string $label = null)
     * @method Show\Field|Collection delivery_user_name(string $label = null)
     * @method Show\Field|Collection delivery_type(string $label = null)
     * @method Show\Field|Collection is_print(string $label = null)
     * @method Show\Field|Collection delivery_id(string $label = null)
     * @method Show\Field|Collection plan_list_detail_id(string $label = null)
     * @method Show\Field|Collection delivery_category(string $label = null)
     * @method Show\Field|Collection delivery_date(string $label = null)
     * @method Show\Field|Collection delivery_log_id(string $label = null)
     * @method Show\Field|Collection total_num(string $label = null)
     * @method Show\Field|Collection total_price(string $label = null)
     * @method Show\Field|Collection delivery_detail_id(string $label = null)
     * @method Show\Field|Collection delivery_paper_id(string $label = null)
     * @method Show\Field|Collection is_price_delete(string $label = null)
     * @method Show\Field|Collection price_status(string $label = null)
     * @method Show\Field|Collection price_at(string $label = null)
     * @method Show\Field|Collection storage_in(string $label = null)
     * @method Show\Field|Collection storage_out(string $label = null)
     * @method Show\Field|Collection spec_id(string $label = null)
     * @method Show\Field|Collection storage_in_status(string $label = null)
     * @method Show\Field|Collection carft_skill_id(string $label = null)
     * @method Show\Field|Collection process_workshop(string $label = null)
     * @method Show\Field|Collection process_department(string $label = null)
     * @method Show\Field|Collection plan_remark(string $label = null)
     * @method Show\Field|Collection dispatch_user_id(string $label = null)
     * @method Show\Field|Collection dispatch_user_name(string $label = null)
     * @method Show\Field|Collection storage_out_status(string $label = null)
     * @method Show\Field|Collection connection(string $label = null)
     * @method Show\Field|Collection queue(string $label = null)
     * @method Show\Field|Collection payload(string $label = null)
     * @method Show\Field|Collection exception(string $label = null)
     * @method Show\Field|Collection failed_at(string $label = null)
     * @method Show\Field|Collection inject_mold_dispatch_paper_id(string $label = null)
     * @method Show\Field|Collection check_user_id(string $label = null)
     * @method Show\Field|Collection image(string $label = null)
     * @method Show\Field|Collection mold_maker_id(string $label = null)
     * @method Show\Field|Collection mold_category_child_id(string $label = null)
     * @method Show\Field|Collection mold_category_parent_id(string $label = null)
     * @method Show\Field|Collection money_from(string $label = null)
     * @method Show\Field|Collection sole_count(string $label = null)
     * @method Show\Field|Collection mold_information_id(string $label = null)
     * @method Show\Field|Collection old_is_void(string $label = null)
     * @method Show\Field|Collection new_is_void(string $label = null)
     * @method Show\Field|Collection void_user_id(string $label = null)
     * @method Show\Field|Collection void_user_name(string $label = null)
     * @method Show\Field|Collection token(string $label = null)
     * @method Show\Field|Collection department_id(string $label = null)
     * @method Show\Field|Collection position_id(string $label = null)
     * @method Show\Field|Collection birthday_at(string $label = null)
     * @method Show\Field|Collection idcard(string $label = null)
     * @method Show\Field|Collection come_at(string $label = null)
     * @method Show\Field|Collection work_at(string $label = null)
     * @method Show\Field|Collection out_at(string $label = null)
     * @method Show\Field|Collection nation(string $label = null)
     * @method Show\Field|Collection plan_category_name(string $label = null)
     * @method Show\Field|Collection client_sole_information_id(string $label = null)
     * @method Show\Field|Collection plan_category_id(string $label = null)
     * @method Show\Field|Collection plan_describe(string $label = null)
     * @method Show\Field|Collection process(string $label = null)
     * @method Show\Field|Collection sole_status(string $label = null)
     * @method Show\Field|Collection inject_mold_status(string $label = null)
     * @method Show\Field|Collection box_label_status(string $label = null)
     * @method Show\Field|Collection from(string $label = null)
     * @method Show\Field|Collection delivery_num(string $label = null)
     * @method Show\Field|Collection storage_out_num(string $label = null)
     * @method Show\Field|Collection delivery_status(string $label = null)
     * @method Show\Field|Collection sole_dispatch_num(string $label = null)
     * @method Show\Field|Collection inject_mold_dispatch_num(string $label = null)
     * @method Show\Field|Collection box_label_dispatch_num(string $label = null)
     * @method Show\Field|Collection sole_dispatch_complete(string $label = null)
     * @method Show\Field|Collection box_label_dispatch_complete(string $label = null)
     * @method Show\Field|Collection delivery_complete(string $label = null)
     * @method Show\Field|Collection raw_material_category_id(string $label = null)
     * @method Show\Field|Collection supplier_id(string $label = null)
     * @method Show\Field|Collection color_id(string $label = null)
     * @method Show\Field|Collection unit_id(string $label = null)
     * @method Show\Field|Collection raw_material_product_information_id(string $label = null)
     * @method Show\Field|Collection purchase_standard_id(string $label = null)
     * @method Show\Field|Collection raw_material_storage_id(string $label = null)
     * @method Show\Field|Collection raw_material_storage_out_id(string $label = null)
     * @method Show\Field|Collection check_time(string $label = null)
     * @method Show\Field|Collection sole_dispatch_paper_id(string $label = null)
     * @method Show\Field|Collection sole_material_color_id(string $label = null)
     * @method Show\Field|Collection sole_workshop_subscribe_no(string $label = null)
     * @method Show\Field|Collection subscribe_remark(string $label = null)
     * @method Show\Field|Collection subscribe_content(string $label = null)
     * @method Show\Field|Collection apply_user_id(string $label = null)
     * @method Show\Field|Collection sole_workshop_subscribe_detail_id(string $label = null)
     * @method Show\Field|Collection old_check_status(string $label = null)
     * @method Show\Field|Collection new_check_status(string $label = null)
     * @method Show\Field|Collection check_reason(string $label = null)
     * @method Show\Field|Collection old_num(string $label = null)
     * @method Show\Field|Collection now_approval_num(string $label = null)
     * @method Show\Field|Collection sole_workshop_subscribe_id(string $label = null)
     * @method Show\Field|Collection purcahse_standard_id(string $label = null)
     * @method Show\Field|Collection approval_num(string $label = null)
     * @method Show\Field|Collection apply_num(string $label = null)
     * @method Show\Field|Collection reason(string $label = null)
     * @method Show\Field|Collection craft_skill_name(string $label = null)
     * @method Show\Field|Collection logger_id(string $label = null)
     * @method Show\Field|Collection logger_name(string $label = null)
     * @method Show\Field|Collection count_type(string $label = null)
     * @method Show\Field|Collection inject_mold_price_id(string $label = null)
     * @method Show\Field|Collection inject_mold_price(string $label = null)
     * @method Show\Field|Collection transit_storage_id(string $label = null)
     * @method Show\Field|Collection out_date(string $label = null)
     * @method Show\Field|Collection trandit_storage_out_id(string $label = null)
     * @method Show\Field|Collection email_verified_at(string $label = null)
     */
    class Show {}

    /**
     * @method \Dcat\Admin\Form\Field\Button button(...$params)
     */
    class Form {}

}

namespace Dcat\Admin\Grid {
    /**
     * @method $this dialog(...$params)
     * @method $this popover(...$params)
     */
    class Column {}

    /**
     * @method \Dcat\Admin\Grid\Filter\MultiInput multiInput(...$params)
     * @method \Dcat\Admin\Grid\Filter\CustomSelect customSelect(...$params)
     */
    class Filter {}
}

namespace Dcat\Admin\Show {
    /**
     
     */
    class Field {}
}
