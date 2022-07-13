var product = {};
var compare_arr = [];
var objs = {

    getUrl: function (page) {
        if(document.domain == 'localhost'){
            var URLHD = location.protocol + "//" + document.domain + "/" + location.pathname.split('/')[0];
        }else{
            var URLHD = location.protocol + "//" + document.domain ; //+ "/" + location.pathname.split('/')[1];
        }
        return URLHD + page;
    }
}   

var SITEURL  = objs.getUrl('/');

if(document.domain == 'localhost'){
     SITEURL  = objs.getUrl('website-drupal-d9/web/');
}
if(drupalSettings.path.baseUrl){
     SITEURL  = drupalSettings.path.baseUrl;
}

product = {
    'sub_total': 0,
    'discount': 0,
    'total': 0,
    'grand_total': 0,
    'items': [],
    'item_count': 0
};

jQuery(function ($) {
    'use strict';
    //DBosta.collection( "compare_list" );
    //var objParams   = { "collection":"compare_list" }
    //var objResult   = DBosta.find(objParams);
    var pm_compare_store_list = localStorage.getItem('CompareListArray');
    
    if(pm_compare_store_list){
        var pm_compareArray = pm_compare_store_list.split(',');
        //var CompareItems    = pm_compareArray;
        pm_compareArray.forEach(function callback(value, index) {
            //console.log(value);
            if(jQuery(".sgp-compare__packing-material").find("[prod-id='" + value + "']")){
                //alert("find"+ value);
                jQuery( "[prod-id='" + value + "']" ).addClass( 'remove-compare' );
                jQuery( "[prod-id='" + value + "']" ).children('input').prop('checked', true);
            }
            compare_arr.push(value);
        });
    }
    jQuery(".sgp-compare__packing-material .sgp-checkbox").click(function() {
        if(jQuery(this).hasClass("remove-compare")){
            //alert("innerdiv");
            var delete_prod_id = jQuery(this).attr('prod-id');
            jQuery(this).removeClass("remove-compare");
            var compareIndex = compare_arr.indexOf(delete_prod_id);
            compare_arr.splice(compareIndex, 1);
            
        }
        else{
            jQuery(this).addClass('remove-compare');
            var prod_id = jQuery(this).attr('prod-id');
            compare_arr.push(prod_id);
        }
        //var compare_data_list  = { "CompareList":compare_arr };
        localStorage.setItem('CompareListArray', compare_arr);
        var queryString = 'CompareData='+compare_arr;
        var staticUrl = SITEURL + 'packing-materials/compare';
        var url = staticUrl + '?' + queryString;
        $("a.sgp-pm-compare_btn").attr("href", url);
        //console.log(url);
        //saveCompareData(compare_data_list);
        //window.sessionStorage.setItem('product_id', prod_id);

    });
    var compare_store_list = localStorage.getItem('CompareListArray');
    if (compare_store_list == null){
        compare_store_list = '';
    }
    //console.log(compare_store_list);
    var queryString = 'CompareData='+compare_store_list;
    var staticUrl = SITEURL + 'packing-materials/compare';
    var url = staticUrl + '?' + queryString;
    $("a.sgp-pm-compare_btn").attr("href", url);
    //console.log(url);
    //var session_data = window.sessionStorage.getItem('product_id');
    //alert(session_data);

    jQuery(document).ready(function () {
        var menu = $(".pm-nav-menu ul li");
        var cboNav = $(".pm-cbo-nav");
        var yourOrder = $(".pm-your-order");
        var cartDetail = $(".pm-cart-detail");
        var backToTop = $(".back-to-top");

        if (cartDetail.length) {
            cartDetail.hide();
        }

        //navFixed();
        itemLoad();

        $(window).scroll(function () {
           // navFixed();
            itemLoad();
            //sidebarFixed();
        });

        $(window).resize(function () {
           // navFixed();
            //sidebarFixed();
        });

        if (menu.length) {
            menu.first().find("a").addClass('active');

            menu.find("a").click(function () {
                menu.find("a.active").removeClass('active');
                $(this).addClass('active');
                if ($(window).width() > 991) {
                    $('html, body').stop().animate({
                        scrollTop: $($(this).attr('href')).offset().top -
                            200
                    }, 400);
                }
                else {
                    $('html, body').stop().animate({
                            scrollTop: $($(this).attr('href')).offset().top - 110
                        },
                        400);
                }

                return false;
            });
        }

        if (backToTop.length) {
            backToTop.find("a").click(function () {
                $('html, body').stop().animate({
                    scrollTop: 0
                }, 400);

                return false;
            })
        }

        if (cboNav.length) {
            var cboOptions = $(".pm-cbo-nav-options");
            cboOptions.hide();

            cboNav.click(function () {
                cboOptions.toggle();
            });

            $(".pm-cbo-nav-option").click(function () {
                $(".pm-cbo-nav-text").text($(this).text());
                cboOptions.hide();
                $('html, body').stop().animate({
                    scrollTop: $($(this).attr('href')).offset().top - 160
                }, 400);

                return false;
            });
        }

        yourOrder.mouseenter(function () {
            if ($(window).width() > 991) {
                $(".toggle-display").fadeIn();
            }
        });

        yourOrder.mouseleave(function () {
            $(".toggle-display").fadeOut();
        });

        yourOrder.click(function () {
            if ($(window).width() > 991) {
                $(".pm-cart-detail").not($(".toggle-display")).fadeOut();
                $(".pm-cart-detail").toggleClass('toggle-display');
                $(this).toggleClass('active');
            }
            else {
                jQuery('html, body').stop().animate({
                    scrollTop: $(".pm-order-form-wrapper").offset().top - 170
                }, 400);
            }

            return false;
        });

        $(".qty-plus").click(function () {
            var input = $(this).parent().find("input");
            var qtyText = $(this).parent().find(".item-qty-text");
            var addToCart = $(this).parent().parent().find(".add-to-cart button.btn-checkout");

            var valueCart = parseInt(parseInt(input.val()) + parseInt(1));
            input.val(valueCart);
            qtyText.text(valueCart);
            if (valueCart > 0) {
                addToCart.removeClass('disabled');
                addToCart.prop('disabled', false);
            }
            else {
                addToCart.addClass('absss');
                addToCart.prop('disabled', true);
            }
        });


        $(".qty-minus").click(function () {
            var input = $(this).parent().find("input");
            var qtyText = $(this).parent().find(".item-qty-text");
            var addToCart = $(this).parent().parent().find(".add-to-cart button.btn-checkout");

            //input.val(Math.max(parseInt(input.val()) - 1, 0));

            var valueCart = parseInt(Math.max(parseInt(input.val()) - 1, 1));
            input.val(valueCart);
            qtyText.text(valueCart);

            if (valueCart > 0) {
                addToCart.prop('disabled', false);
                addToCart.removeClass('disabled');
            }
            else {
                addToCart.prop('disabled', true);
                addToCart.addClass('disabled');
            }
        });

        $("input.item-qty, input.item-qty:disabled").keypress(function () {
            if (event.charCode == 13) {
                $(this).blur();
            }
            else {
                return event.charCode >= 48 && event.charCode <= 57;
            }

        });

        $("input.item-qty").keyup(function () {
            var addToCart = $(this).parent().parent().find(".add-to-cart button.btn-checkout");

            if ($(this).val().length == 0) {
                $(this).val(0);
                addToCart.prop('disabled', true);
                addToCart.addClass('disabled');
            }
            else {
                $(this).val(parseInt(jQuery(this).val()));
                if ($(this).val() > 0) {
                    addToCart.prop('disabled', false);
                    addToCart.removeClass('disabled');
                }
                else {
                    addToCart.prop('disabled', true);
                    addToCart.addClass('disabled');
                }
            }
        });

        $(".add-to-cart button.btn-checkout").click(function () {
            //console.log('working');
            var bundleQty = $(this).parent().parent().find(".bundle-qty");
            var itemDetail = $(this).parent().parent().find(".item-detail");
            var itemQty = bundleQty.find(".item-qty");
            var qtyText = bundleQty.find(".item-qty-text");
            /*console.log('bundleQty'+bundleQty);
            console.log('working');
            console.log(itemDetail);*/
            if (itemQty.val() > 0) {
                var id = itemDetail.find(".item-id").text();
                var category = itemDetail.find(".item-category").text();
                var name = itemDetail.find(".item-name").text();
                var image_url = itemDetail.find(".item-image").text();
                var dimension = itemDetail.find(".item-dimension").text();
                var price = parseFloat(itemDetail.find(".item-discounted-price .item-price").text());
                var qty = parseInt(itemQty.val());
                var bundle = itemDetail.find(".item-bundle").text();
                var discountedPrice = parseFloat(itemDetail.find(".discounted-price").text());

                var totalPrice = Math.round(price * qty * 100) / 100;
                var totalDiscountedPrice = Math.round(discountedPrice * qty * 100) / 100;

                // var tmp  //= new Array();
                var item =  {  
                                 "bundle": bundle,
                                    "category": category,
                                    "dimension": dimension ,
                                    "discountedPrice": discountedPrice,
                                    "id": id,
                                    "name": name ,
                                    "price": price ,
                                    "image":image_url,
                                    "qty": qty ,
                                    "totalDiscountedPrice": totalDiscountedPrice , 
                                    "totalPrice": totalPrice 
                            }
                

                var cartToData       = { "cartItems":item }

                //console.log(cartToData);
                productCalculate(cartToData);

       
            }

            return false;
        });

        $(".pm-tbl-cart").on('click', 'a.pm-item-edit', function () {
            $(this).text('Update');
            $(this).siblings("input.pm-order-item-qty").prop('disabled', false).focus();

            return false;
        });

        $(".order-detail-products").on('click', 'a.pm-item-edit', function () {
            $(this).text('Update');
            $(this).parent().parent().find("input.pm-order-item-qty").prop('disabled', false).focus();

            return false;
        });

        $(".order-detail-products, .pm-tbl-cart").on('keypress', 'input.pm-order-item-qty', function () {
            if (event.charCode == 13) {
                $(this).blur();
            }
            else {
                return event.charCode >= 48 && event.charCode <= 57;
            }
        });

        $(".order-detail-products, .pm-tbl-cart").on('focusout', 'input.pm-order-item-qty', function () {
            product['items'][$(this).parent().parent().attr('id')]['qty'] = Math.max(parseInt($(this).val()), 1);

            productCalculate(product);
            updateCart(product);
            updateForm(product);
        });

        $(".pm-tbl-cart, .order-detail-products").on('click', 'a.pm-item-remove', function () {
            removeItem($(this).attr('id'));

            return false;
        });

        $("#pm-cart-checkout").click(function () {
            $('html, body').stop().animate({
                scrollTop: $('.pm-order-form-wrapper').offset().top - 130
            }, 400);

            $('form.pm-frontend-order-form').show();
            $('#pm-form-processed').hide();
        });

        $('#pm-form-processed').click(function () {
            $(this).hide();
            $('form.pm-frontend-order-form').show();
        });

        $(".view-order").click(function () {
            $('#message-add-to-cart').modal('hide');
            if ($(window).width() > 991) {
                $(".pm-cart-detail").fadeIn();
            }
            else {
                $('html, body').stop().animate({
                    scrollTop: $(".pm-order-form-wrapper").offset().top - 170
                }, 400);
            }
        });

        $(document.body).on('click', '.close_cart', function () {
            //console.log('click');
            $('.cartBlocks').removeClass('active');
            return false;
        });

   $(document.body).on('click', '.qty-update-pop', function (event) {
            //event.preventDefault();
            var action     = $(this).data('action'),
                min        = $(this).data('min'),
                max        = $(this).data('max'),
                //event      = $(this).data('event'),
                id         =  parseInt($(this).data('idx')),
                idx        =  $(this).data('idx');
            var temp = {
                        'sub_total': 0,
                        'discount': 0,
                        'item_count': 0,
                        'grand_total': 0
             };
         var qty=parseInt($('#qty_cart'+id).val());
         //console.log(qty);
        // return false;
         if(action){qty=(qty+1)<=(max|0)?qty+1:(max|0);}
         else{qty=(qty-1)>=(min|0)?qty-1:(min|0);}
         $('#qty_cart'+id).val(qty);
         $('#qntD'+id).text(qty);
         $('#qty_txt'+id).text(qty);
        // console.log(qty);
        // $.mobile.changePage( "#cart");
         //ga_track('Item','Quantity',qty);
        // return false;
            DBosta.collection( "cart_data" );
            var objParams   = { "collection":"cart_data" }
            var objResult   = DBosta.find(objParams);
            //console.log(objResult);
            if(objResult.length>0){



                var cartItemsSx    = objResult[0].data.cartItems;
                //console.log(cartItemsSx.length);

                 //console.log(cartItemsSx);
              
                var result = cartItemsSx.findIndex( ({ id }) => id == idx);

                  //console.log('result'+result);
                  //console.log('id'+id);
                if (( typeof result === "undefined" ) || (result == -1) || (result === -1)) {
                   // console.log('result not match');
                }else{
                     var cartItemsSxi    = cartItemsSx[result];
                     cartItemsSxi['qty'] = qty;

                     //console.log(cartItemsSxi);
                     objResult[0].data.cartItems[result] = cartItemsSxi;
                     var updatedCartIn = objResult[0].data.cartItems;
                     //console.log(updatedCartIn);
//return false;
                if(updatedCartIn.length>0){
                
                    
                 updatedCartIn.forEach(function callback(value, index) {
 
                                    value.totalPrice = Math.round((parseFloat((value.price) * value.qty)) * 100) / 100;
                                    value.totalDiscountedPrice = Math.round((parseFloat((value.discountedPrice) * value.qty)) * 100) / 100;
                                    temp['sub_total'] += value.totalPrice;
                                    temp['discount'] +=  value.totalPrice - value.totalDiscountedPrice
                                    temp['grand_total'] += value.totalDiscountedPrice
                                    temp['item_count'] += value.qty;
                         
                });
 
                               var sub_total    = Math.round(temp['sub_total'] * 100) / 100,
                                   discount     = Math.round(temp['discount'] * 100) / 100,
                                   grand_total  = Math.round(temp['grand_total'] * 100) / 100,
                                   item_count   = temp['item_count'];

                               var cartToData   = { 
                                                    "sub_total": sub_total, 
                                                    "discount" : discount,
                                                    "grand_total":grand_total,
                                                    "item_count":item_count,
                                                    "cartItems":updatedCartIn 
                                                  };
                                 
                                saveCartData(cartToData);
 
                    viewCart();
                 }else{
                 }

                }
               
                
                 return false;
 
                
            }
          });

 

        $(document.body).on('click', '.btn-delete', function () {
            var id = $(this).attr('id');
           // console.log(id);
            //$('#row'+id).hide();
            var temp = {
                        'sub_total': 0,
                        'discount': 0,
                        'item_count': 0,
                        'grand_total': 0
             };

              var getCartData       = getCartDatas();
                if(getCartData === false){ 
                    return false; 
                }
                DBosta.collection( "cart_data" );
                var objParams   = { "collection":"cart_data" }
                
                var cartItemsSx    = getCartData.data.cartItems;
                
                if(cartItemsSx.length == 1){
                    DBosta.remove(objParams);
                     viewCart();
                    return false;
                }
                
                 cartItemsSx = jQuery.grep(cartItemsSx, function(value) {
                         return value.id != id;
                });
                
                if(cartItemsSx.length>0){
                                   
                            cartItemsSx.forEach(function callback(value, index) {
 
                                    value.totalPrice = Math.round((parseFloat((value.price) * value.qty)) * 100) / 100;
                                    value.totalDiscountedPrice = Math.round((parseFloat((value.discountedPrice) * value.qty)) * 100) / 100;
                                    temp['sub_total'] += value.totalPrice;
                                    temp['discount'] +=  value.totalPrice - value.totalDiscountedPrice
                                    temp['grand_total'] += value.totalDiscountedPrice
                                    temp['item_count'] += value.qty;
                         
                                });
 
                               var sub_total    = Math.round(temp['sub_total'] * 100) / 100,
                                   discount     = Math.round(temp['discount'] * 100) / 100,
                                   grand_total  = Math.round(temp['grand_total'] * 100) / 100,
                                   item_count   = temp['item_count'];

                               var cartToData   = { 
                                                    "sub_total": sub_total, 
                                                    "discount" : discount,
                                                    "grand_total":grand_total,
                                                    "item_count":item_count,
                                                    "cartItems":cartItemsSx 
                                                  };
                                saveCartData(cartToData);
 
                    viewCart();
                 }else{
                 }
                

            return false;
        });


    });


function getCartDatas()
{
    DBosta.vDebug = false;
    //prepare collection to be used (creates if non existant)
    DBosta.collection( "cart_data" );
    var objParams   = { "collection":"cart_data" }
    var objResult   = DBosta.find(objParams); 
    if(objResult.length>0){
        return objResult[0];
    }else{
        return false;
    }
}
    function navFixed() {
        var scroll = jQuery(window).scrollTop();
        var banner = jQuery(".banner");
        var navWrapper = jQuery(".pm-nav-wrapper");
        var underNav = jQuery(".pm-under-nav");

        if (jQuery(window).width() > 991) {
            if (banner.offset().top + banner.height() < scroll) {
                navWrapper.addClass('pm-fixed-nav');
                underNav.removeClass('pm-fixed-nav');
            }
            else {
                navWrapper.removeClass('pm-fixed-nav');
            }
        }
        else {
            if (banner.offset().top + banner.height() +
                jQuery(".pm-above-desc").height() + jQuery("header").height() < scroll) {
                navWrapper.removeClass('pm-fixed-nav');
                underNav.addClass('pm-fixed-nav');
            }
            else {
                underNav.removeClass('pm-fixed-nav');
            }
        }
    }

    function sidebarFixed() {
        var scroll = jQuery(window).scrollTop();
        var banner = jQuery(".banner");
        var pmSidebar = jQuery(".pm-sidebar");
        var aside = jQuery("aside");
        var recent = jQuery(".recent-visited");

        if (jQuery(window).width() >= 1200) {
            if (scroll + aside.height() > recent.offset().top - 100) {
                aside.css({top: (scroll + aside.height() - recent.offset().top + 100) * -1})
            }
            else if (banner.offset().top + banner.height() < scroll) {
                pmSidebar.width(jQuery(".col-xl-3").width());
                aside.addClass('pm-fixed-sidebar');
                aside.css({top: '100px'})
            }
            else {
                aside.removeClass('pm-fixed-sidebar');
            }
        }
        else {
            pmSidebar.css({'width': ''});
            aside.removeClass('pm-fixed-sidebar');
        }
    }

    function itemLoad() {
        var scroll = jQuery(window).scrollTop();
        jQuery(".pm-item-group, .grid-item").each(function () {
            if (jQuery(this).offset().top < scroll + jQuery(window).height()) {
                jQuery(this).animate({
                    opacity: 1
                }, 1000);
            }
        });
    }

    function productCalculate(data) {

        var temp = {};
        var CartDataItems    = [];
        temp = {
            'sub_total': 0,
            'discount': 0,
            'item_count': 0,
            'grand_total': 0
        };


         DBosta.collection( "cart_data" );
         var objParamsCd = { "collection":"cart_data" }
         var objResultCD = DBosta.find(objParamsCd);

             if(objResultCD.length>0){
                  // console.log(objResultCD);
                    var cartDataInDB         = objResultCD[0].data.cartItems;
                    var cartDataInDB1        = objResultCD[0].data;
                    var CartLenght           = cartDataInDB.length;
                    var CurrentCartData      = data.cartItems;


                   // var result = cartDataInDB.find( ({ id }) => id === CurrentCartData.id );

                   var result = cartDataInDB.findIndex( ({ id }) => id === CurrentCartData.id);


                 //  console.log(result);
                    if (( typeof result === "undefined" ) || (result == -1) || (result === -1)) {
                            CartDataItems[CartLenght] = CurrentCartData;
                            cartDataInDB.splice(CartLenght, 0, CurrentCartData);
                            CartDataItems = cartDataInDB;
                        }else{
                            

                            CurrentCartData.qty = parseInt(CurrentCartData.qty) + parseInt(cartDataInDB[result]['qty']);
                            cartDataInDB[result] = CurrentCartData;
                            CartDataItems = cartDataInDB; 

 

                        }
     

                     
                        
               }else{

                    DBosta.remove(objParamsCd);
                    CartDataItems[0]     = data.cartItems;
              }
        
         

        CartDataItems.forEach(function callback(value, index) {
           
            value.totalPrice = Math.round((parseFloat((value.price) * value.qty)) * 100) / 100;
            value.totalDiscountedPrice = Math.round((parseFloat((value.discountedPrice) * value.qty)) * 100) / 100;
            temp['sub_total'] += value.totalPrice;
            temp['discount'] +=  value.totalPrice - value.totalDiscountedPrice
            temp['grand_total'] += value.totalDiscountedPrice
            temp['item_count'] += value.qty;
 
        });

        //data['items'] = tmp;

       var sub_total    = Math.round(temp['sub_total'] * 100) / 100,
           discount     = Math.round(temp['discount'] * 100) / 100,
           grand_total  = Math.round(temp['grand_total'] * 100) / 100,
           item_count   = temp['item_count'];

       var cartToData   = { 
                            "sub_total": sub_total, 
                            "discount" : discount,
                            "grand_total":grand_total,
                            "item_count":item_count,
                            "cartItems":CartDataItems 
                          };
        saveCartData(cartToData);
        viewCart();
        openCartBlock();
        // var objFields        = {  "collection":"cart_data","fields":{ "cartDatas":cartToData }}; //build first record to insert
        //console.log(JSON.stringify(cartToData));
        

/*         data['items'].forEach(function (item) {
            item['totalPrice'] = Math.round((parseFloat((item['price']) * item['qty'])) * 100) / 100;
            item['totalDiscountedPrice'] = Math.round((parseFloat((item['discountedPrice']) * item['qty'])) * 100) / 100;
            temp['sub_total'] += item['totalPrice'];
            temp['discount'] += item['totalPrice'] - item['totalDiscountedPrice']
            temp['grand_total'] += item['totalDiscountedPrice']
            temp['item_count'] += item['qty'];
        });

        product['sub_total'] = Math.round(temp['sub_total'] * 100) / 100;
        product['discount'] = Math.round(temp['discount'] * 100) / 100;
        product['grand_total'] = Math.round(temp['grand_total'] * 100) / 100;
        product['item_count'] = temp['item_count'];*/

       // jQuery(".badge-notify").text(product['item_count']);
    }


function saveCartData(data){
   // console.log(data);
    DBosta.collection( "cart_data" );
    var objParamsD  = { "collection":"cart_data" }
    var objFieldsD  = { "collection":"cart_data","fields":{ "data":data }};
    DBosta.remove(objParamsD);
    var updatedCx   = DBosta.register(objFieldsD);
  // console.log();
}

function viewcartDataOnSummaryPage(objResult){
        $('.viewCartSmAllDetails').show();
         $('.ProceedToCheckout').show();
           var TotalItemsInCart = objResult[0].data.cartItems.length;
       var CartDataItems    = objResult[0].data.cartItems;
       var data_all         = objResult[0].data;
       var datasHm ='';
            datasHm +='<div class="sgp-cart__box">';
                 datasHm +='<div class="sgp-cart__box-row sgp-cart__box-row--head">';
                     datasHm +='<div class="sgp-cart__item-col">Item</div>';
                     datasHm +='<div class="sgp-cart__qty-col">Qty (Bundle)</div>';
                     datasHm +='<div class="sgp-cart__qty-col">Total</div>';
                 datasHm +='</div>';

       CartDataItems.forEach(function callback(value, index) {
            var total_row = parseInt( value.price * value.qty);
            datasHm +='<div class="sgp-cart__box-row" id="row'+value.id+'">';
                    datasHm +='<div class="sgp-cart__prdct-img">';
                        datasHm +='<img src="'+value.image+'" alt="'+value.name+'" class="sgp-cart__prdct-image">';
                    datasHm +='</div>';
                    datasHm +='<div class="sgp-cart__prdct-detail">';
                        datasHm +='<div class="sgp-cart__prdct-head">';
                            datasHm +='<div class="sgp-cart__prdct-title">'+value.category+'</div>';
                            datasHm +='<h3 class="sgp-cart__prdct-name">'+value.name+'</h3>';
                        datasHm +='</div>';
                        datasHm +='<div class="sgp-cart__prdct-qty">';
                            datasHm +='<div class="sgp-qty">';
                                datasHm +='<button class="sgp-qty__btn qty-minus-pop qty-update-pop" data-action="0" data-min="1" data-max="25" data-event="event" data-idx="'+value.id+'">';
                                datasHm +='<svg xmlns="http://www.w3.org/2000/svg" width="12.25" height="1.531" viewBox="0 0 12.25 1.531">';
                                        datasHm +='<path d="M11.922-4.484H.328A.316.316,0,0,1,.1-4.58.316.316,0,0,1,0-4.812v-.875A.316.316,0,0,1,.1-5.92a.316.316,0,0,1,.232-.1H11.922a.316.316,0,0,1,.232.1.316.316,0,0,1,.1.232v.875a.316.316,0,0,1-.1.232A.316.316,0,0,1,11.922-4.484Z" transform="translate(0 6.016)" />';
                                    datasHm +='</svg></button>';
                                datasHm +='<span class="item-qtyp-text" id="qty_txt'+value.id+'" >'+value.qty+'</span>';
                                datasHm +='<input type="hidden" id="qty_cart'+value.id+'" name="qnt" value="'+value.qty+'"/>';
                                datasHm +='<button class="sgp-qty__btn qty-plus-pop qty-update-pop" data-action="1" data-min="1" data-max="25" data-event="event" data-idx="'+value.id+'">';
                                datasHm +='<svg xmlns="http://www.w3.org/2000/svg" width="12.25" height="12.25" viewBox="0 0 12.25 12.25">';
                                        datasHm +='<path d="M11.922-6.016a.316.316,0,0,1,.232.1.316.316,0,0,1,.1.232v.875a.316.316,0,0,1-.1.232.316.316,0,0,1-.232.1H6.891V.547a.316.316,0,0,1-.1.232.316.316,0,0,1-.232.1H5.688a.316.316,0,0,1-.232-.1.316.316,0,0,1-.1-.232V-4.484H.328A.316.316,0,0,1,.1-4.58.316.316,0,0,1,0-4.812v-.875A.316.316,0,0,1,.1-5.92a.316.316,0,0,1,.232-.1H5.359v-5.031a.316.316,0,0,1,.1-.232.316.316,0,0,1,.232-.1h.875a.316.316,0,0,1,.232.1.316.316,0,0,1,.1.232v5.031Z" transform="translate(0 11.375)" />';
                                    datasHm +='</svg></button>';
                            datasHm +='</div>';
                          // datasHm +=' <button class="sgp-cart__qty-reload">';
                            //    datasHm +='<img src="'+SITEURL+'themes/singpostd9/assets/images/refresh-icon.svg" alt="refresh icon">';
                           // datasHm +='</button>';
                        datasHm +='</div>';
                        datasHm +='<div class="sgp-cart__prdct-price">';
                            datasHm +='<div class="sgp-cart__prdct-price-value">S$'+total_row+'</div>';
                            datasHm +='<button title="Delete" class="sgp-cart__delete btn-delete" id="'+value.id+'">';
                                datasHm +='<img src="'+SITEURL+'themes/singpostd9/assets/images/delete-red-icon.svg" alt="delete icon">';
                            datasHm +='</button>';
                        datasHm +='</div>';
                    datasHm +='</div>';
                datasHm +='</div>';

       });
       datasHm +='</div>';


        //viewCartSmAllDetails
        $('.cartBlocksSummary').html(datasHm);
        $('.viewSmSubTotal').text('S$'+data_all.sub_total);
        $('.viewSmDiscount').text('- S$'+data_all.discount);
        $('.viewSmTotalAmt').text('S$'+data_all.grand_total);
        if (TotalItemsInCart > 1) {
            $('.badge-notify').text(TotalItemsInCart +' item(s)');
        }else{
            $('.badge-notify').text(TotalItemsInCart +' item(s)');
        }

}

function viewCartDataOnShow(objResult){

       var TotalItemsInCart = objResult[0].data.cartItems.length;
       var CartDataItems    = objResult[0].data.cartItems;
       var data_all         = objResult[0].data;
       var datasHm ='';
        datasHm +='<div class="sgp-minicart__head"><h4 class="sgp-h4">My Cart</h4><button class="sgp-minicart__close close_cart"><svg xmlns="http://www.w3.org/2000/svg" width="9.461" height="9.461" viewBox="0 0 9.461 9.461"><path id="Path_1027" data-name="Path 1027" d="M6.344-5.25,9.9-1.7a.4.4,0,0,1,.082.246A.3.3,0,0,1,9.9-1.23L9.27-.6a.3.3,0,0,1-.219.082A.4.4,0,0,1,8.8-.6L5.25-4.156,1.7-.6a.4.4,0,0,1-.246.082A.3.3,0,0,1,1.23-.6L.6-1.23A.3.3,0,0,1,.52-1.449.4.4,0,0,1,.6-1.7L4.156-5.25.6-8.8A.4.4,0,0,1,.52-9.051.3.3,0,0,1,.6-9.27L1.23-9.9a.3.3,0,0,1,.219-.082A.4.4,0,0,1,1.7-9.9L5.25-6.344,8.8-9.9a.4.4,0,0,1,.246-.082A.3.3,0,0,1,9.27-9.9L9.9-9.27a.3.3,0,0,1,.082.219A.4.4,0,0,1,9.9-8.8Z" transform="translate(-0.52 9.98)"></path></svg></button></div>';
       datasHm +='<div class="sgp-minicart__item-wrapper" id="cartItemInBox">';
        datasHm +='<div class="sgp-minicart__item sgp-minicart__empty">';
           // datasHm +='<p class="sgp-minicart__empty-text">Cart Empty</p>';
        datasHm +='</div>';

       CartDataItems.forEach(function callback(value, index) {
        var total_row = parseInt( value.price * value.qty);
            datasHm +='<div class="sgp-minicart__item" id="row'+value.id+'">';
             datasHm +='<div class="sgp-minicart__img-sec">';
                datasHm +='<img src="'+value.image+'" alt="'+value.name+'">';
                datasHm +='</div>';
                    datasHm +='<div class="sgp-minicart__cart-sec">';
                        datasHm +='<div class="sgp-minicart__product">';
                            datasHm +='<h6 class="sgp-h6">'+value.name+'</h6>';
                            datasHm +='<p class="sgp-minicart__product-price">S$'+total_row+'</p>';
                        datasHm +='</div>';
                        datasHm +='<div class="sgp-minicart__update">'; 
                            datasHm +='<div class="sgp-qty">';
                                datasHm +='<button class="sgp-qty__btn qty-minus-pop qty-update-pop" data-action="0" data-min="1" data-max="25" data-event="event" data-idx="'+value.id+'">';
                                datasHm +='<svg xmlns="http://www.w3.org/2000/svg" width="12.25" height="1.531" viewBox="0 0 12.25 1.531">';
                                datasHm +='<path d="M11.922-4.484H.328A.316.316,0,0,1,.1-4.58.316.316,0,0,1,0-4.812v-.875A.316.316,0,0,1,.1-5.92a.316.316,0,0,1,.232-.1H11.922a.316.316,0,0,1,.232.1.316.316,0,0,1,.1.232v.875a.316.316,0,0,1-.1.232A.316.316,0,0,1,11.922-4.484Z" transform="translate(0 6.016)"/>';
                                datasHm +='</svg>';
                                datasHm +='</button>';
                                datasHm +='<span class="item-qtyp-text" id="qty_txt'+value.id+'" >'+value.qty+'</span>';
                                datasHm +='<input type="hidden" id="qty_cart'+value.id+'" name="qnt" value="'+value.qty+'"/>';
                                datasHm +='<button class="sgp-qty__btn qty-plus-pop qty-update-pop"  data-action="1" data-min="1" data-max="25" data-event="event" data-idx="'+value.id+'">';
                                datasHm +='<svg xmlns="http://www.w3.org/2000/svg" width="12.25" height="12.25" viewBox="0 0 12.25 12.25">';
                                datasHm +='<path d="M11.922-6.016a.316.316,0,0,1,.232.1.316.316,0,0,1,.1.232v.875a.316.316,0,0,1-.1.232.316.316,0,0,1-.232.1H6.891V.547a.316.316,0,0,1-.1.232.316.316,0,0,1-.232.1H5.688a.316.316,0,0,1-.232-.1.316.316,0,0,1-.1-.232V-4.484H.328A.316.316,0,0,1,.1-4.58.316.316,0,0,1,0-4.812v-.875A.316.316,0,0,1,.1-5.92a.316.316,0,0,1,.232-.1H5.359v-5.031a.316.316,0,0,1,.1-.232.316.316,0,0,1,.232-.1h.875a.316.316,0,0,1,.232.1.316.316,0,0,1,.1.232v5.031Z" transform="translate(0 11.375)"/>';
                                datasHm +='</svg>';
                                datasHm +='</button>';
                            datasHm +='</div>';
                           // datasHm +='<button class="sgp-minicart__reset-btn" id="refresh-'+value.id+'">';
                             //   datasHm +='<img src="'+SITEURL+'themes/singpostd9/assets/images/reset-icon-blue.svg" alt="">';
                           // datasHm +='</button>';
                            datasHm +='<button class="sgp-minicart__del-btn btn-delete" id="'+value.id+'">';
                                datasHm +='<img src="'+SITEURL+'themes/singpostd9/assets/images/delete-icon-red.svg" alt="">';
                            datasHm +='</button>';
                        datasHm +='</div>';
                    datasHm +='</div>';
                datasHm +='</div>';
        });
datasHm +='</div>';
datasHm +='<div class="sgp-minicart__amount-sec">';
    datasHm +='<p class="sgp-minicart__price-detail">Subtotal: <span class="subtotal">S$'+data_all.sub_total+'</span></p>';
    datasHm +='<p class="sgp-minicart__price-detail">Discount: <span class="discount"> - S$'+data_all.discount+'</span></p>';
    datasHm +='<p class="sgp-minicart__price-detail sgp-minicart__total">Total Amount: <span class="totalAmt">S$'+data_all.grand_total+'</span></p>';
datasHm +='</div>';
datasHm +='<div class="sgp-minicart__btn-sec">';
    datasHm +='<a href="'+SITEURL+'packing-materials/cart-summary" title="View Cart" class="sgp-link-btn sgp-link-btn--border">View Cart</a>';
    datasHm +='<a href="'+SITEURL+'packing-materials/cart-checkout" title="Checkout" class="sgp-link-btn sgp-link-btn--box">Checkout</a>';
datasHm +='</div>';
datasHm +='<p class="sgp-minicart__note">You need a min. spend of <strong>S$150</strong> to make an online order. For orders below <strong>S$150</strong>, visit any Post Office islandwide!</p>';


$('.cartBlocks').html(datasHm);
$('.subtotal').text('S$'+data_all.sub_total);
$('.discount').text('- S$'+data_all.discount);
$('.totalAmt').text('S$'+data_all.grand_total);
if (TotalItemsInCart > 1) {
    $('.badge-notify').text(TotalItemsInCart +' item(s)');
}else{
    $('.badge-notify').text(TotalItemsInCart +' item(s)');
}

//
//viewCart();

}
function openCartBlock(){
    $('.cartBlocks').addClass('active');
}
function cartEmpty(){
  
    if (window.location.href.indexOf("cart-thanks") > -1) {
         
            DBosta.collection( "cart_data" );
            var objParamsD  = { "collection":"cart_data" }
            DBosta.remove(objParamsD);
    } 
}
viewCart();
function viewCart(){
    //console.log('here');
        cartEmpty();
        DBosta.collection( "cart_data" );
        var objParams   = { "collection":"cart_data" }
        var objResult   = DBosta.find(objParams);
        if(objResult.length>0){
             viewCartDataOnShow(objResult);
             viewcartDataOnSummaryPage(objResult);
             $('#edit-product_dt').val(JSON.stringify(objResult[0].data));
        }else{
            var datasHm = '';
            var datasHmi = '';
             datasHm +='<div class="sgp-minicart__item sgp-minicart__empty">';
               datasHm +='<p class="sgp-minicart__empty-text">Your cart is empty!</p>';
                 datasHm +='</div>';
                   $('.cartBlocks').html(datasHm);

             datasHmi +='<div class="sgp-cart__box">';
                 datasHmi +='<p class="sgp-minicart__empty-text align-center">Your cart is empty!</p>';
                datasHmi +='<a href="'+SITEURL+'packing-materials" title="Add Items" class="sgp-add-item sgp-mt-4">';
                    datasHmi +='<img src="'+SITEURL+'themes/singpostd9/assets/images/plus-icon.svg" alt="+">Add Items </a>';
             datasHmi +='</div>';
                   $('.cartBlocksSummary').html(datasHmi);
                   $('.badge-notify').text('0 item(s)');
                    $('.viewCartSmAllDetails').hide();
                    $('.ProceedToCheckout').hide();
                    //console.log('Your cart is empty!'); 
                    if (window.location.href.indexOf("cart-checkout") > -1) {
                      
                        $('.sgp-checkout-fromx').html(datasHmi);
                    }    
        }
}




    function updateCart(data) {
        jQuery("tr.pm-cart-item").remove();

        data['items'].forEach(function (item) {
            var htmlCart = '<tr class="pm-cart-item" id="' + item['id'] + '">' +
                '<td>' + item['name'] + '<br>Qty: <input type="text" class="pm-item-qty pm-order-item-qty" value="' + item['qty'] + '" disabled><br><a class="pm-item-edit" id="' + item['id'] + '" href="">Edit</a><span class="block-text-body text-color-primary"> | </span><a class="pm-item-remove" id="' + item['id'] + '" href="#">Remove</a></td>' +
                '<td>$' + (Math.round(item['totalPrice'] * 100) / 100) + "</td></tr>";

            jQuery("tr#pm-cart-break").before(htmlCart);
        });

        jQuery(".pm-cart-sub-total td:last-child").text("S$" + product['sub_total']);
        jQuery(".pm-cart-discount td:last-child").text("S$" + product['discount'].toFixed(2));
        jQuery(".pm-cart-grand-total td:last-child").text("S$" + product['grand_total'].toFixed(2));

        if (product['grand_total'] >= 150) {
            jQuery("button#pm-cart-checkout").prop('disabled', false);
            jQuery("button#pm-cart-checkout").removeClass('disabled');
        }
        else {
            jQuery("button#pm-cart-checkout").prop('disabled', true);
            jQuery("button#pm-cart-checkout").addClass('disabled');
        }
    }

    function updateForm(data) {
        jQuery("tr.pm-order-form-item").remove();
        jQuery(".form-item-detail").html('');
        //console.log(data);
        data['items'].forEach(function (item) {
            var htmlCart = '<tr class="pm-order-form-item" id="' + item['id'] + '">' +
                '<td class="pm-order-form-item_btn">' + item['name'] + '<br><a class="pm-item-edit" id="' + item['id'] + '" href="">Edit</a><span class="block-text-body text-color-primary"> | </span><a class="pm-item-remove" id="' + item['id'] + '" href="#">Remove</a></td>' +
                '<td class="text-center"><input type="text" class="pm-item-qty pm-order-item-qty text-center" value="' + item['qty'] + '" disabled></td>' +
                '<td class="text-center">' + (parseFloat(item['bundle']) * parseInt(item['qty'])) + '</td>' +
                '<td class="text-center">$' + (Math.round(item['totalPrice'] * 100) / 100) + '</td>' +
                '<td class="text-center">$' + (Math.round(item['totalDiscountedPrice'] * 100) / 100) + '</td>' +
                '</tr>';

            jQuery(".order-detail-products tbody").append(htmlCart);

            var htmlInput = '<input type="hidden" name="detail[' + item['id'] + '][bundle]" value="' + item['bundle'] + '">' +
                '<input type="hidden" name="detail[' + item['id'] + '][discountedPrice]" value="' + item['discountedPrice'] + '">' +
                '<input type="hidden" name="detail[' + item['id'] + '][price]" value="' + item['price'] + '">' +
                '<input type="hidden" name="detail[' + item['id'] + '][name]" value="' + item['name'] + '">' +
                '<input type="hidden" name="detail[' + item['id'] + '][qty]" value="' + item['qty'] + '">';

            jQuery(".form-item-detail").append(htmlInput);

        });

        jQuery(".odf-sub-total").text("$" + product['sub_total']);
        jQuery(".odf-discount").text("$" + product['discount'].toFixed(2));
        jQuery(".odf-grand-total").text("$" + product['grand_total'].toFixed(2));

        var total = '<input type="hidden" name="detail[sub_total]" value="' + product['sub_total'] + '">' +
            '<input type="hidden" name="detail[discount]" value="' + product['discount'] + '">' +
            '<input type="hidden" name="detail[total]" value="' + product['grand_total'] + '">';

        jQuery(".form-item-detail").append(total);

        if (product['grand_total'] >= 150) {
            jQuery("button#pm-form-processed").prop('disabled', false);
            jQuery("button#pm-form-processed").removeClass('disabled');
            jQuery(".pm-processed-notice").hide();
        }
        else {
            jQuery("button#pm-form-processed").show();
            jQuery("button#pm-form-processed").prop('disabled', true);
            jQuery("button#pm-form-processed").addClass('disabled');
            jQuery(".pm-processed-notice").show();
            jQuery('form.pm-frontend-order-form').hide();
        }
    }

    function removeItem(id) {
        delete product['items'][id];
        productCalculate(product);
        updateCart(product);
        updateForm(product);
    }
});

