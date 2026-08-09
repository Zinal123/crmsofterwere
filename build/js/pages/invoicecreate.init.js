var paymentSign = "₹";
Array.from(document.getElementsByClassName("product-line-price")).forEach(function(item) {
    item.value = paymentSign + "0.00"
});
Array.from(document.getElementsByClassName("less-discount-amount")).forEach(function(item) {
    item.value = paymentSign + "0.00"
});

function otherPayment() {
    var paymentType = document.getElementById("choices-payment-currency").value;
    paymentSign = paymentType;


    Array.from(document.getElementsByClassName("product-line-price")).forEach(function(item) {
        isUpdate = item.value.slice(1);
        item.value = paymentSign + isUpdate;
    });

    Array.from(document.getElementsByClassName("less-discount-amount")).forEach(function(item) {
        isUpdate = item.value.slice(1);
        item.value = paymentSign + isUpdate;

    });

    recalculateCart();
}

var isPaymentEl = document.getElementById("choices-payment-currency");
var choices = new Choices(isPaymentEl, {
    searchEnabled: false
});

// Profile Img
document
    .querySelector("#profile-img-file-input")
    .addEventListener("change", function() {
        var preview = document.querySelector(".user-profile-image");
        var file = document.querySelector(".profile-img-file-input").files[0];
        var reader = new FileReader();
        reader.addEventListener(
            "load",
            function() {
                preview.src = reader.result;
                //localStorage.setItem("invoiceLogo", reader.result);
            },
            false
        );
        if (file) {
            reader.readAsDataURL(file);
        }
    });

flatpickr("#date-field", {
    enableTime: true,
    dateFormat: "d M, Y, h:i K",
});

isData();

function isData() {
    var plus = document.getElementsByClassName("plus");

    minus = document.getElementsByClassName("minus");

    if (plus) {

        Array.from(plus).forEach(function(e) {
            e.onclick = function(event) {
                if (parseInt(e.previousElementSibling.value) < 2400) {
                    event.target.previousElementSibling.value++;
                    var itemAmount = e.parentElement.parentElement.previousElementSibling.querySelector(".product-price").value;

                    var priceselection = e.parentElement.parentElement.nextElementSibling.querySelector(".product-line-price");
                    var lessdiscount1 = e.parentElement.parentElement.nextElementSibling.querySelector(".less-discount-amount");
                    var productQty = e.parentElement.querySelector(".product-quantity").value;
                    // var lessdiscount = e.parentElement.querySelector(".less-discount-amount");
                    updateQuantity(productQty, itemAmount, priceselection, lessdiscount1);
                }
            }
        });

    }

    if (minus) {
        Array.from(minus).forEach(function(e) {
            e.onclick = function(event) {
                if (parseInt(e.nextElementSibling.value) > 1) {
                    event.target.nextElementSibling.value--;
                    var itemAmount = e.parentElement.parentElement.previousElementSibling.querySelector(".product-price").value;
                    var priceselection = e.parentElement.parentElement.nextElementSibling.querySelector(".product-line-price");

                    // var productQty = 1;
                    var productQty = e.parentElement.querySelector(".product-quantity").value;
                    updateQuantity(productQty, itemAmount, priceselection);
                }
            };
        });
    }
}

function otherPayment1() {

    const paymentType1 = document.getElementById('productName-' + count + '').value;



    $.ajax({
        type: "get",
        url: "https://cms.oraclemachinetech.com/admin/invoice/getproduct1",
        data: {
            paymentType1: paymentType1,


        },
        dataType: "json",
        contentType: "application/json",


        success: function(response) {
            var hsn = response['make'];
            var unit = response['unit'];;
            var rate = response['rate'];
            document.getElementById('hsn-' + count + '').value = (hsn);
            document.getElementById('unit-' + count + '').value = (unit);
            document.getElementById('productRate-' + count + '').value = (rate);
        }
    });





}
var count = 1;

function new_link() {
    const elements = document.getElementById('productName-' + count + '').value;

    for (let i = 0; i < elements.length; i++) {
        elements[i].textContent = ''; // Remove the element
    }

    $.ajax({
        type: "get",
        url: "https://cms.oraclemachinetech.com/admin/invoice/getproductvalue",
        dataType: "json",
        contentType: "application/json",


        success: function(response) {
            console.log(response)
            $('.item').append('<option value="0">Select product</option>');
            $.each(response.product, function(index, value) {
                // APPEND OR INSERT DATA TO SELECT ELEMENT.

                $('.item').append('<option value="' + value.id + '">' + value.name + '</option>');
            });
        }
    });

    count++;
    var tr1 = document.createElement("tr");
    tr1.id = count;
    tr1.className = "product";


    var delLink =
        "<tr>" +
        '<th scope="row" class="product-id">' +
        count +
        "</th>" +
        '<td class="text-start">' +
        '<div class="mb-2">' +
        '<select class="form-select item" data-choices data-choices-sorting="true" name ="productName" id = "productName-' + count + '" onchange="otherPayment1(' + count + ')">' +


        '</select>' +
        // '<input class="form-control bg-light border-0" type="text" id="productName-' + count + '">' +
        '</div>' +

        "</div>" +
        "</td>" +
        '<td class="text-end">' +
        "<div>" +
        '<input type="text" class="form-control bg-light border-0 hsn" id="hsn-' + count + '"/>' +
        "</div>" +
        "</td>" +

        '<td class="text-end">' +
        "<div>" +
        '<input type="text" class="form-control bg-light border-0 unit" id="unit-' + count + '" placeholder="$0.00" />' +
        "</div>" +
        "</td>" +
        "<td>" +
        '<input class="form-control product-price bg-light border-0" type="number" id="productRate-' + count + '" step="0.01" placeholder="$0.00">' +
        "</td>" +
        "<td>" +
        '<div class="input-step">' +
        '<button type="button" class="minus">–</button>' +
        '<input type="number" class="product-quantity" id="product-qty-' + count + '" value="0" readonly>' +
        '<button type="button" class="plus">+</button>' +
        "</div>" +
        "</td>" +
        '<td class="text-end">' +
        "<div>" +
        '<input type="text" class="form-control bg-light border-0 product-line-price" id="productPrice-' + count + '"  placeholder="$0.00" >' +
        "</div>" +
        "</td>" +
        '<td class="text-end">' +
        "<div>" +
        '<select class="form-select bg-light border-0 gst" id="gst-' + count + '">' +
        '<option value="0">0%</option>' +
        '<option value="5">5%</option>' +
        '<option value="12">12%</option>' +
        '<option value="18" selected>18%</option>' +
        '<option value="28">28%</option>' +
        "</select>" +
        "</div>" +
        "</td>" +
        '<td class="text-end">' +
        "<div>" +
        '<input type="text" class="form-control bg-light border-0 withtax" id="withtax-' + count + '"  placeholder="$0.00" >' +
        "</div>" +
        "</td>" +
        '<td class="text-end">' +
        "<div>" +
        '<input type="text" class="form-control bg-light border-0 total" id="total-' + count + '"  placeholder="$0.00" >' +
        "</div>" +
        "</td>" +
        '<td class="product-removal">' +
        '<a class="btn btn-danger">Delete</a>' +
        "</td>" +
        "</tr>";
    tr1.innerHTML = document.getElementById("newForm").innerHTML + delLink;

    document.getElementById("newlink").appendChild(tr1);
    var genericExamples = document.querySelectorAll("[data-trigger]");
    Array.from(genericExamples).forEach(function(genericExamp) {
        var element = genericExamp;
        new Choices(element, {
            placeholderValue: "This is a placeholder set in the config",
            searchPlaceholderValue: "This is a search placeholder",
        });
    });

    isData();
    remove();
    amountKeyup();
    resetRow();
    recalculateCart()

}

remove();
/* Set rates + misc */
var taxRate = 0.18;
var shippingRate = 65.0;




function resetRow() {

    Array.from(document.getElementById("newlink").querySelectorAll("tr")).forEach(function(subItem, index) {
        var incid = index + 1;
        subItem.querySelector('.product-id').innerHTML = incid;

    });
}

function remove() {
    Array.from(document.querySelectorAll(".product-removal a")).forEach(function(el) {
        el.addEventListener("click", function(e) {
            removeItem(e);
            resetRow()

        });
    });
}

/* Recalculate cart */
function recalculateCart() {
    var subtotal = 0;
    var subtotal1 = 0;
    var taxable = 0;




    Array.from(document.getElementsByClassName("product")).forEach(function(item) {
        Array.from(item.getElementsByClassName("product-line-price")).forEach(function(e) {

            if (e.value) {
                subtotal += parseFloat(e.value);
            }


        });

    });
    Array.from(document.getElementsByClassName("product")).forEach(function(item) {
        Array.from(item.getElementsByClassName("withtax")).forEach(function(e) {

            if (e.value) {
                subtotal1 += parseFloat(e.value);
            }


        });


    });
    Array.from(document.getElementsByClassName("product")).forEach(function(item) {
        Array.from(item.getElementsByClassName("total")).forEach(function(e) {

            if (e.value) {
                taxable += parseFloat(e.value);
            }


        });


    });
    var totalamountbeforetax = subtotal + subtotal1;
    document.getElementById("cart-totalbeforetax").value = paymentSign + subtotal1.toFixed(2);



    // var totalamountbeforetax = subtotal - subtotal1;
    document.getElementById("cart-total").value = paymentSign + subtotal.toFixed(2);
    document.getElementById("cart-amount").value = paymentSign + taxable.toFixed(2);
    // document.getElementById("cart-total").value = paymentSign + subtotal.toFixed(2);







}



function amountKeyup() {

    // var listArray = [];

    // listArray.push(document.getElementsByClassName('product-price'));
    Array.from(document.getElementsByClassName('product-price')).forEach(function(item) {
        item.addEventListener('keyup', function(e) {

            var priceselection = item.parentElement.nextElementSibling.nextElementSibling.querySelector('.product-line-price');


            var amount = e.target.value;


            var itemQuntity = item.parentElement.nextElementSibling.querySelector('.product-quantity').value;



            updateQuantity(amount, itemQuntity, priceselection);

        });
    });
}

amountKeyup();
/* Update quantity */
function updateQuantity(amount, itemQuntity, priceselection) {
    var linePrice = amount * itemQuntity;
    linePrice = linePrice.toFixed(2);
    priceselection.value = linePrice;
    var gstSelect = document.getElementById('gst-' + count + '');
    var lineGstRate = gstSelect ? (parseFloat(gstSelect.value) / 100) : 0.18;
    taxableamount = linePrice * lineGstRate;
    taxableamount1 = taxableamount.toFixed(2);
    taxableamount2 = paymentSign + taxableamount1;
    const num1 = parseInt(linePrice);
    const num2 = parseInt(taxableamount);
    taxableamount4 = num1 + num2;
    taxableamount5 = taxableamount4.toFixed(2);
    document.getElementById('withtax-' + count + '').value = (taxableamount1);
    document.getElementById('total-' + count + '').value = (taxableamount5);
    recalculateCart();
}

/* Remove item from cart */
function removeItem(removeButton) {
    removeButton.target.closest("tr").remove();
    recalculateCart();
}

//Choise Js
var genericExamples = document.querySelectorAll("[data-trigger]");
Array.from(genericExamples).forEach(function(genericExamp) {
    var element = genericExamp;
    new Choices(element, {
        placeholderValue: "This is a placeholder set in the config",
        searchPlaceholderValue: "This is a search placeholder",
    });
});

//Address
function billingFunction() {
    if (document.getElementById("same").checked) {
        document.getElementById("shippingName").value =
            document.getElementById("billingName").value;
        document.getElementById("shippingAddress").value =
            document.getElementById("billingAddress").value;
        document.getElementById("shippingPhoneno").value =
            document.getElementById("billingPhoneno").value;
        document.getElementById("billingstate").value =
            document.getElementById("shippingstate").value;
        document.getElementById("billinggst").value =
            document.getElementById("shippinggst").value
        document.getElementById("billingpan").value =
            document.getElementById("shippingpan").value;

    } else {
        document.getElementById("shippingName").value = "";
        document.getElementById("shippingAddress").value = "";
        document.getElementById("shippingPhoneno").value = "";
        document.getElementById("shippingstate").value = "";
        document.getElementById("shippinggst").value = "";
        document.getElementById("shippingpan").value = "";
    }
}


var cleaveBlocks = new Cleave('#cardNumber', {
    blocks: [4, 4, 4, 4],
    uppercase: true
});

var genericExamples = document.querySelectorAll('[data-plugin="cleave-phone"]');
Array.from(genericExamples).forEach(function(genericExamp) {
    var element = genericExamp;
    new Cleave(element, {
        delimiters: ['(', ')', '-'],
        blocks: [0, 3, 3, 4]
    });
});

let viewobj;
var invoices_list = localStorage.getItem("invoices-list");
var options = localStorage.getItem("option");
var invoice_no = localStorage.getItem("invoice_no");

var invoices = JSON.parse(invoices_list);

if (localStorage.getItem("invoice_no") === null && localStorage.getItem("option") === null) {
    viewobj = '';
    var value = "#VL" + Math.floor(11111111 + Math.random() * 99999999);
    document.getElementById("invoicenoInput").value = value;
}

// Invoice Data Load On Form
if ((viewobj != '') && (options == "edit-invoice")) {


    document.getElementById("companyEmail").value = viewobj.company_details.email;
    document.getElementById('companyWebsite').value = viewobj.company_details.website;
    new Cleave("#compnayContactno", {
        prefix: viewobj.company_details.contact_no,
        delimiters: ['(', ')', '-'],
        blocks: [0, 3, 3, 4]
    });
    document.getElementById("companyAddress").value = viewobj.company_details.address;
    document.getElementById("companyaddpostalcode").value = viewobj.company_details.zip_code;

    var preview = document.querySelectorAll(".user-profile-image");
    if (viewobj.img !== '') {
        preview.src = viewobj.img;
    }

    document.getElementById("invoicenoInput").value = "#VAL" + viewobj.invoice_no;
    document.getElementById("invoicenoInput").setAttribute('readonly', true);
    document.getElementById("date-field").value = viewobj.date;
    document.getElementById("choices-payment-status").value = viewobj.status;
    document.getElementById("cart-amount").value = "$" + viewobj.order_summary.total_amount;

    document.getElementById("billingName").value = viewobj.billing_address.full_name;
    document.getElementById("billingAddress").value = viewobj.billing_address.address;
    new Cleave("#billingPhoneno", {
        prefix: viewobj.company_details.contact_no,
        delimiters: ['(', ')', '-'],
        blocks: [0, 3, 3, 4]
    });
    document.getElementById("billingstate").value = viewobj.billing_address.state;
    document.getElementById("billinggst").value = viewobj.billing_address.gst;
    document.getElementById("billingpan").value = viewobj.billing_address.pan;


    document.getElementById("shippingName").value = viewobj.shipping_address.full_name;
    document.getElementById("shippingAddress").value = viewobj.shipping_address.address;
    new Cleave("#shippingPhoneno", {
        prefix: viewobj.company_details.contact_no,
        delimiters: ['(', ')', '-'],
        blocks: [0, 3, 3, 4]
    });
    document.getElementById("shippingstate").value = viewobj.shipping_address.state;
    document.getElementById("shippinggst").value = viewobj.shipping_address.gst;
    document.getElementById("shippingpan").value = viewobj.shipping_address.pan;



    var paroducts_list = viewobj.prducts;
    var counter = 1;
    do {
        counter++;
        if (paroducts_list.length > 1) {
            document.getElementById("add-item").click();
        }
    } while (paroducts_list.length - 1 >= counter);

    var counter_1 = 1;

    setTimeout(() => {
        Array.from(paroducts_list).forEach(function(element) {
            document.getElementById("productName-" + counter_1).value = element.product_name;
            document.getElementById("hsn-" + counter_1).value = element.hsn;
            document.getElementById("unit-" + counter_1).value = element.unit;
            document.getElementById("productRate-" + counter_1).value = element.rates;
            document.getElementById("product-qty-" + counter_1).value = element.quantity;
            document.getElementById("productPrice-" + counter_1).value = ((element.rates) * (element.quantity));
            document.getElementById("gst-" + counter_1).value = element.gst;
            document.getElementById("withtax-" + counter_1).value = element.withtax;
            document.getElementById("total-" + counter_1).value = element.withtax;





            counter_1++;
        });
    }, 300);
    0
    document.getElementById("cart-totalbeforetax").value = "$" + viewobj.order_summary.sub_total;

    document.getElementById("cart-total").value = "$" + viewobj.order_summary.total_amount;
    document.getElementById("cart-amount").value = "$" + viewobj.order_summary.total_amount;





    document.getElementById("exampleFormControlTextarea1").value = viewobj.notes;

}

document.addEventListener("DOMContentLoaded", function() {

    // //Form Validation
    var formEvent = document.getElementById('invoice_form');
    var forms = document.getElementsByClassName('needs-validation');


    // Loop over them and prevent submission
    formEvent.addEventListener("submit", function(event) {
        event.preventDefault();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }


        });
        // get fields value
        var i_no = document.getElementById("invoicenoInput").value;
        var company_details_companyAddress = document.getElementById("companyAddress").value;
        var company_details_companyaddpostalcode = document.getElementById("companyaddpostalcode").value;
        var company_details_email = document.getElementById("companyEmail").value;
        var company_details_Website = document.getElementById("companyWebsite").value;
        var company_details_contact_no = document.getElementById("compnayContactno").value;
        var date = document.getElementById("date-field").value;
        var paycondition = document.getElementById("paycondition").value;
        var duedate = document.getElementById("duedate").value;
        var placesupply = document.getElementById("placesupply").value;
        var challanno = document.getElementById("challanno").value;
        var challandate = document.getElementById("challandate").value;
        var ponumber = document.getElementById("ponumber").value;
        var ewaybillno = document.getElementById("ewaybillno").value;
        var ewaybilldate = document.getElementById("ewaybilldate").value;
        var transportvehicle = document.getElementById("transportvehicle").value;
        var despatchthrough = document.getElementById("despatchthrough").value;
        var billing_address_full_name = document.getElementById("billingName").value;
        var billing_address_address = document.getElementById("billingAddress").value;
        var billing_address_phone = (document.getElementById("billingPhoneno").value).replace(/[^0-9]/g, "");
        var billing_state = document.getElementById("billingstate").value;
        var billing_gst = document.getElementById("billinggst").value;
        var billing_pan = document.getElementById("billingpan").value;
        var shipping_address_full_name = document.getElementById("shippingName").value;
        var shipping_address_address = document.getElementById("shippingAddress").value;
        var shipping_address_phone = (document.getElementById("shippingPhoneno").value).replace(/[^0-9]/g, "");
        var shipping_state = document.getElementById("shippingstate").value;
        var shipping_gst = document.getElementById("shippinggst").value;
        var shipping_pan = document.getElementById("shippingpan").value;

        var order_summary_cart_totalbeforetax = (document.getElementById("cart-totalbeforetax").value).slice(1);

        var order_summary_cart_total = (document.getElementById("cart-total").value).slice(1);

        var order_summary_cart_amount = (document.getElementById("cart-amount").value).slice(1);
        var payment_details_card_holder_name = document.getElementById("cardholderName").value;

        var payment_details_card_number = document.getElementById("cardNumber").value;
        var payment_details_card_ifsc_code = document.getElementById("cardifsccode").value;
        var payment_details_bank_name = document.getElementById("bankname").value;
        var payment_details_branch_name = document.getElementById("bankbranchname").value;
        var notes = document.getElementById("exampleFormControlTextarea1").value;
        var products = document.getElementsByClassName("product");
        var count = 1;
        var new_product_obj = [];
        Array.from(products).forEach(element => {
            var product_name = element.querySelector("#productName-" + count).value;
            var hsn = element.querySelector("#hsn-" + count).value;
            var unit = element.querySelector("#unit-" + count).value;
            var product_rate = parseInt(element.querySelector("#productRate-" + count).value);
            var product_qty = parseInt(element.querySelector("#product-qty-" + count).value);
            var product_price = parseInt(element.querySelector("#productPrice-" + count).value);
            var withtax = (element.querySelector("#withtax-" + count).value);
            var gst = (element.querySelector("#gst-" + count).value);
            var total = (element.querySelector("#total-" + count).value);



            var product_obj = {
                product_name: product_name,
                hsn: hsn,
                unit: unit,
                product_rate: product_rate,
                product_qty: product_qty,
                withtax: withtax,
                gst: gst,
                total: total,
                product_price: parseInt(product_price)

            }

            new_product_obj.push(product_obj);

            count++;
        });

        if (formEvent.checkValidity() === false) {
            formEvent.classList.add("was-validated");
        } else {
            if ((options == "edit-invoice") && (invoice_no == i_no)) {
                objIndex = invoices.findIndex((obj => obj.invoice_no == i_no));

                invoices[objIndex].invoice_no = i_no;
                invoices[objIndex].date = date;
                invoices[objIndex].paycondition = paycondition;
                invoices[objIndex].duedate = duedate;
                invoices[objIndex].placesupply = placesupply;
                invoices[objIndex].challanno = challanno;
                invoices[objIndex].challandate = challandate;
                invoices[objIndex].ponumber = ponumber;
                invoices[objIndex].transportvehicle = transportvehicle;
                invoices[objIndex].despatchthrough = despatchthrough;
                invoices[objIndex].ewaybillno = ewaybillno;
                invoices[objIndex].ewaybilldate = ewaybilldate;
                invoices[objIndex].total = order_summary_cart_total,
                    invoices[objIndex].totalamountbeforetax = order_summary_cart_totalbeforetax,
                    invoices[objIndex].total_amount = order_summary_total_amount,
                    invoices[objIndex].card_holder_name = payment_details_card_holder_name,
                    invoices[objIndex].card_number = payment_details_card_number,
                    invoices[objIndex].card_ifsc = payment_details_card_ifsc_code,
                    invoices[objIndex].bank_name = payment_details_bank_name,
                    invoices[objIndex].bank_branch_name = payment_details_branch_name,
                    invoices[objIndex].full_name = billing_address_full_name,
                    invoices[objIndex].address = billing_address_address,
                    invoices[objIndex].phone = billing_address_phone,
                    invoices[objIndex].state = billing_state,
                    invoices[objIndex].gst = billing_gst,
                    invoices[objIndex].pan = billing_pan,
                    invoices[objIndex].s_full_name = shipping_address_full_name,
                    invoices[objIndex].s_address = shipping_address_address,
                    invoices[objIndex].s_phone = shipping_address_phone,
                    invoices[objIndex].s_state = shipping_state,
                    invoices[objIndex].s_gst = shipping_gst,
                    invoices[objIndex].s_pan = shipping_pan,

                    invoices[objIndex].email = company_details_email,
                    invoices[objIndex].website = company_details_Website,
                    invoices[objIndex].contact_no = company_details_contact_no,
                    invoices[objIndex].address = company_details_companyAddress,
                    invoices[objIndex].zip_code = company_details_companyaddpostalcode;







                invoices[objIndex].prducts = new_product_obj;
                invoices[objIndex].notes = notes;

                localStorage.removeItem("invoices-list");
                localStorage.removeItem("option");
                localStorage.removeItem("invoice_no");
                localStorage.setItem("invoices-list", JSON.stringify(invoices));
            } else {
                var new_data_object = {
                    invoice_no: i_no,
                    date: date,
                    paycondition: paycondition,
                    duedate: duedate,
                    customer: billing_address_full_name,
                    placesupply: placesupply,
                    challanno: challanno,
                    challandate: challandate,
                    ponumber: ponumber,
                    transportvehicle: transportvehicle,
                    despatchthrough: despatchthrough,
                    ewaybillno: ewaybillno,
                    ewaybilldate: ewaybilldate,
                    order_summary_cart_amount: order_summary_cart_amount,
                    order_summary_cart_total: order_summary_cart_total,
                    order_summary_cart_totalbeforetax: order_summary_cart_totalbeforetax,
                    payment_details_card_holder_name: payment_details_card_holder_name,
                    payment_details_card_number: payment_details_card_number,
                    payment_details_card_ifsc_code: payment_details_card_ifsc_code,
                    payment_details_bank_name: payment_details_bank_name,
                    payment_details_branch_name: payment_details_branch_name,
                    billing_address_full_name: billing_address_full_name,
                    billing_address_address: billing_address_address,
                    billing_address_phone: billing_address_phone,
                    billing_state: billing_state,
                    billing_gst: billing_gst,
                    billing_pan: billing_pan,
                    shipping_address_full_name: shipping_address_full_name,
                    shipping_address_address: shipping_address_address,
                    shipping_address_phone: shipping_address_phone,
                    shipping_state: shipping_state,
                    shipping_gst: shipping_gst,
                    shipping_pan: shipping_pan,
                    company_details_email: company_details_email,
                    company_details_Website: company_details_Website,
                    company_details_contact_no: company_details_contact_no,
                    company_details_companyAddress: company_details_companyAddress,
                    company_details_companyaddpostalcode: company_details_companyaddpostalcode,








                    new_product_obj: new_product_obj,
                    notes: notes,


                };
                var myJsonString = JSON.stringify(new_data_object);
                var obj = JSON.parse(myJsonString);




                $.ajax({
                    url: "https://cms.oraclemachinetech.com/invoicestore",
                    method: 'post',
                    data: {
                        invoice_id: obj.invoice_no,
                        invoice_date: obj.date,
                        paycondition: obj.paycondition,
                        duedate: obj.duedate,
                        placesupply: obj.placesupply,
                        challanno: obj.challanno,
                        challandate: obj.challandate,
                        ponumber: obj.ponumber,
                        transportvehicle: obj.transportvehicle,
                        despatchthrough: obj.despatchthrough,
                        ewaybillno: obj.ewaybillno,
                        ewaybilldate: obj.ewaybilldate,
                        notes: obj.notes,
                        order_summary_cart_total: obj.order_summary_cart_total,
                        order_summary_cart_totalbeforetax: obj.order_summary_cart_totalbeforetax,
                        order_summary_cart_amount: obj.order_summary_cart_amount,
                        payment_details_card_holder_name: obj.payment_details_card_holder_name,
                        payment_details_card_number: obj.payment_details_card_number,
                        payment_details_card_ifsc_code: obj.payment_details_card_ifsc_code,
                        payment_details_bank_name: obj.payment_details_bank_name,
                        payment_details_branch_name: obj.payment_details_branch_name,
                        billing_address_full_name: obj.billing_address_full_name,
                        billing_address_address: obj.billing_address_address,
                        billing_address_phone: obj.billing_address_phone,
                        billing_state: obj.billing_state,
                        billing_gst: billing_gst,
                        billing_pan: billing_pan,
                        shipping_address_full_name: shipping_address_full_name,
                        shipping_address_address: shipping_address_address,
                        shipping_address_phone: shipping_address_phone,
                        shipping_state: shipping_state,
                        shipping_gst: shipping_gst,
                        shipping_pan: shipping_pan,
                        company_details_email: obj.company_details_email,
                        company_details_Website: obj.company_details_Website,
                        company_details_contact_no: obj.company_details_contact_no,
                        company_details_companyAddress: obj.company_details_companyAddress,
                        company_details_companyaddpostalcode: obj.company_details_companyaddpostalcode,
                        new_product_obj: new_product_obj,
                    },

                    dataType: 'json',

                    success: function(res) {
                        window.location.href = "https://cms.oraclemachinetech.com/apps-invoices-list";

                    },
                    error: function(xhr) {
                        var message = (xhr.responseJSON && xhr.responseJSON.message) || "Something went wrong while saving the invoice. Please check the form and try again.";
                        Swal.fire({
                            icon: 'error',
                            title: 'Unable to save invoice',
                            text: message
                        });
                    }
                });

            }

        }
    });
});