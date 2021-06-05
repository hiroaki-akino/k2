// 送信確認の関数
function check(){
    if(window.confirm('送信してよろしいですか？')){ // 確認ダイアログを表示
        return true; // 「OK」時は送信を実行
    }else{ // 「キャンセル」時の処理
        window.alert('キャンセルされました'); // 警告ダイアログを表示
        return false; // 送信を中止
    }
}
// 要素取得の省略関数
function get(id){
    return document.getElementById(id);
}
// 非表示の省略関数
function display_none(element){
        element.style.display = "none";
        return;
}
// 再表示の省略関数
function display_block(element){
        element.style.display = "block";
        return;
}
// タブ切り替え関数
function tab_change(type,character){
    var forEach = Array.prototype.forEach;
    var section = document.getElementsByTagName('section');
    forEach.call(section, function(element){
        element.style.display = "none";
    });
    switch(type){
        case "buyer":
            var target = document.getElementById("buyer_mypage_section1" + character);
        break;
        case "seller":
            var target = document.getElementById("seller_mypage_section1" + character);
        break;
    }
    target.style.display = "block";
}
// 入力情報確認の非表示関数
function confirm_chenge(type){
    switch(type){
        case "buyer":
            var div_post1		= get("d_buyer_postalcode");
            var div_address1	= get("d_buyer_address_1");
            var div_address2	= get("d_buyer_address_2");
            var div_address3	= get("d_buyer_address_3");
            var div_user_name	= get("d_buyer_name");
            var form_btn1		= get("btn1");
            var form_btn2		= get("btn2");
            var form_btn3		= get("btn3");
            var val_post1		= get("buyer_high_postalcode").value;
            var val_post2		= get("buyer_low_postalcode").value;
            var val_address1	= get("buyer_address_1").value;
            var val_address2	= get("buyer_address_2").value;
            var val_address3	= get("buyer_address_3").value;
            var val_user_name	= get("buyer_name").value;
            length1             = val_post1.length;
            length2             = val_post2.length;
            while(true){
                if(val_post1 == ""){
                    alert("入力されていないフォームがあります");
                    return;
                }
                if(val_post2 == ""){
                    alert("入力されていないフォームがあります");
                    return;
                }
                if(isNaN(Number(val_post1)) || isNaN(Number(val_post2))){
                    alert("郵便番号には数値を入力してください");
                    return;
                }
                if(length1 != 3 || length2 != 4){
                    alert("郵便番号は3桁-4桁で入力してください");
                    return;
                }
                if(val_address1 == ""){
                    alert("入力されていないフォームがあります");
                    return;
                }
                if(val_address2 == ""){
                    alert("入力されていないフォームがあります");
                    return;
                }
                if(val_address3 == ""){
                    alert("入力されていないフォームがあります");
                    return;
                }
                if(val_user_name == ""){
                    alert("入力されていないフォームがあります");
                    return;
                }
                break;
            }
            display_none(div_post1);
            get("d_buyer_postalcode_r").textContent  = val_post1 + "－" + val_post2;
            display_none(div_address1);
            get("d_buyer_address_1_r").textContent        = val_address1;
            display_none(div_address2);
            get("d_buyer_address_2_r").textContent		= val_address2;
            display_none(div_address3);
            get("d_buyer_address_3_r").textContent		= val_address3;
            display_none(div_user_name);
            get("d_buyer_name_r").textContent 			= val_user_name;
            display_none(form_btn1);
            display_block(form_btn2);
            display_block(form_btn3);
        break;
        case "seller":
            var div_post1		= get("d_seller_postalcode");
            var div_address1	= get("d_seller_address_1");
            var div_address2	= get("d_seller_address_2");
            var div_address3	= get("d_seller_address_3");
            var div_user_name	= get("d_seller_name");
            var div_office_name	= get("d_seller_office_name");
            var form_btn1		= get("btn1");
            var form_btn2		= get("btn2");
            var form_btn3		= get("btn3");
            var val_post1		= get("seller_high_postalcode").value;
            var val_post2		= get("seller_low_postalcode").value;
            var val_address1	= get("seller_address_1").value;
            var val_address2	= get("seller_address_2").value;
            var val_address3	= get("seller_address_3").value;
            var val_user_name	= get("seller_name").value;
            var val_office_name = get("seller_office_name").value;
            length1             = val_post1.length;
            length2             = val_post2.length;
            while(true){
                if(val_post1 == ""){
                    alert("入力されていないフォームがあります");
                    return;
                }
                if(val_post2 == ""){
                    alert("入力されていないフォームがあります");
                    return;
                }
                if(isNaN(Number(val_post1)) || isNaN(Number(val_post2))){
                    alert("郵便番号には数値を入力してください");
                    return;
                }
                if(length1 != 3 || length2 != 4){
                    alert("郵便番号は3桁-4桁で入力してください");
                    return;
                }
                if(val_address1 == ""){
                    alert("入力されていないフォームがあります");
                    return;
                }
                if(val_address2 == ""){
                    alert("入力されていないフォームがあります");
                    return;
                }
                if(val_address3 == ""){
                    alert("入力されていないフォームがあります");
                    return;
                }
                if(val_user_name == ""){
                    alert("入力されていないフォームがあります");
                    return;
                }
                break;
            }
            display_none(div_post1);
            get("d_seller_postalcode_r").textContent    = val_post1 + "－" + val_post2;
            display_none(div_address1);
            get("d_seller_address_1_r").textContent     = val_address1;
            display_none(div_address2);
            get("d_seller_address_2_r").textContent		= val_address2;
            display_none(div_address3);
            get("d_seller_address_3_r").textContent		= val_address3;
            display_none(div_user_name);
            get("d_seller_name_r").textContent 			= val_user_name;
            display_none(div_office_name);
            get("d_seller_office_name_r").textContent     = val_office_name;
            display_none(form_btn1);
            display_block(form_btn2);
            display_block(form_btn3);
        break;
    }
}
// 再入力の表示関数
function rewrite_change(type){
    switch(type){
        case "buyer":
            var div_post1		= get("d_buyer_postalcode");
            var div_address1	= get("d_buyer_address_1");
            var div_address2	= get("d_buyer_address_2");
            var div_address3	= get("d_buyer_address_3");
            var div_user_name	= get("d_buyer_name");
            var form_btn1		= get("btn1");
            var form_btn2		= get("btn2");
            var form_btn3		= get("btn3");
            display_block(div_post1);
            get("d_buyer_postalcode_r").textContent     = "";
            display_block(div_address1);
            get("d_buyer_address_1_r").textContent		= "";
            display_block(div_address2);
            get("d_buyer_address_2_r").textContent		= "";
            display_block(div_address3);
            get("d_buyer_address_3_r").textContent		= "";
            display_block(div_user_name);
            get("d_buyer_name_r").textContent 			= "";
            display_block(form_btn1);
            display_none(form_btn2);
            display_none(form_btn3);        
        break;
        case "seller":
            var div_post1		= get("d_seller_postalcode");
            var div_address1	= get("d_seller_address_1");
            var div_address2	= get("d_seller_address_2");
            var div_address3	= get("d_seller_address_3");
            var div_user_name	= get("d_seller_name");
            var div_office_name = get("d_seller_office_name");
            var form_btn1		= get("btn1");
            var form_btn2		= get("btn2");
            var form_btn3		= get("btn3");
            display_block(div_post1);
            get("d_seller_postalcode_r").textContent    = "";
            display_block(div_address1);
            get("d_seller_address_1_r").textContent		= "";
            display_block(div_address2);
            get("d_seller_address_2_r").textContent		= "";
            display_block(div_address3);
            get("d_seller_address_3_r").textContent		= "";
            display_block(div_user_name);
            get("d_seller_name_r").textContent 			= "";
            display_block(div_office_name);
            get("d_seller_office_name_r").textContent 	= "";
            display_block(form_btn1);
            display_none(form_btn2);
            display_none(form_btn3);
        break;
    }
}