var bkItemsList;

var vendorID = 1;
var bkVendor = document.getElementById('AUWLBkVendorID');
if (bkVendor && bkVendor.innerHTML && bkVendor.innerHTML > 0){
    vendorID = bkVendor.innerHTML;
}

var AddToAUWLButton = document.getElementById('AddToAUWLButton');
if(AddToAUWLButton) {
    function AddToAUWL() {
        var s;
        if(typeof AUWLBook=='undefined') {
            s=document.createElement('script');
            s.setAttribute('src','https://www.amazon.com/registry/add.js?vendor=' + vendorID);
            document.body.appendChild(s);
        }
        function f(){(typeof AUWLBook=='undefined')?setTimeout(f,200):AUWLBook.showPopover({name: ''});};
        f();
    };
    var link = document.createElement('A');
    link.href = 'javascript:AddToAUWL()';
    link.title = 'Add to Amazon Wish List';
    var image = document.createElement('IMG');
    image.style.border = 'none';
    image.src = "/images/amazon-icon.png";
    image.style.marginRight="5px";
    image.className = "pull-left";
    link.appendChild(image);
    link.innerHTML +="Add To Amazon Baby Registry";
    AddToAUWLButton.parentNode.insertBefore(link,AddToAUWLButton);
}