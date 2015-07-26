<?

class Zend_Service_Ebay_Shopping_Item extends Zend_Service_Ebay_Finding_Search_Item {
	
	public $autoPay;
	public $charityId;
	public $country;
	public $distance;
	public $globalId;
	public $itemId;
	public $listingInfo;
	public $location;
	public $paymentMethod;
	public $postalCode;
	public $primaryCategory;
	public $productId;
	public $secondaryCategory;
	public $sellerInfo;
	public $sellingStatus;
	public $shippingInfo;
	public $storeInfo;
	public $subtitle;
	public $title;
	public $viewItemURL;
	
	
	
	
	
	public function __construct($xml)
	{
		//Zend_Debug::dump($xml);
		//print_r($xml); die;
		
		$this->description = (string) $xml->Description;
		$this->title = (string) $xml->Title;
		
		$this->autoPay = (boolean) $xml->AutoPay;
		
		$this->charityId = '';//$xml->;
		$this->country = (string) $xml->Country; //$xml->Site;
		$this->distance = '';//$xml->;
		$this->galleryURL =  (string) $xml->GalleryURL;
		
		foreach ($xml->PictureURL as $k => $v) {
        	$this->pictureURL[] = (string) $v;
        }
	
		
		$this->globalId = 'EBAY-' . (string) $xml->Site;
		$this->itemId = (string) $xml->ItemID;


  
		$listingInfo = array();
		$listingInfo['bestOfferEnabled'] = (string) $xml->BestOfferEnabled;
		//$listingInfo['buyItNowAvailable'] = (string) $xml->BuyItNowAvailable;
		//$listingInfo['buyItNowPrice'] = (string) $xml->BuyItNowPrice;
		//$listingInfo['convertedBuyItNowPrice'] = (string) $xml->ConvertedBuyItNowPrice;


        //$listingInfo['convertedCurrentPrice'] = (string) $xml->ConvertedCurrentPrice;
        //$listingInfo['convertedCurrentPrice_currencyId'] = (string) $xml->ConvertedCurrentPrice->attributes()->currencyID;;


		$listingInfo['endTime'] = (string) $xml->EndTime;
		$listingInfo['gift'] = '';
		$listingInfo['listingType'] = (string) $xml->ListingType;
		$listingInfo['startTime'] = (string) $xml->StartTime;
		$listingInfo['quantity'] = (string) $xml->Quantity;
		$listingInfo['quantitySold'] = (string) $xml->QuantitySold;

		//$listingInfo['buyItNowPrice_currencyId'] = (string) $xml->BuyItNowPrice->attributes()->currencyID;
		//$listingInfo['convertedBuyItNowPrice_currencyId'] = (string) $xml->ConvertedBuyItNowPrice->attributes()->currencyID;
		$listingInfo['returnPolicy'] = (string) $xml->ReturnPolicy->ReturnsAccepted;
		$listingInfo['handlingTime'] = (string) $xml->HandlingTime;



		$this->listingInfo = (object) $listingInfo;
		
		$condition = array();
		$condition['conditionID'] = (string) $xml->ConditionID;
		$condition['conditionDisplayName'] = (string) $xml->ConditionDisplayName;
		$this->condition = (object) $condition;
				
		$this->location = (string) $xml->Location;
		$this->paymentMethod = (array) $xml->PaymentMethods;
		$this->postalCode = (string) $xml->PostalCode;
		
		$primaryCategory = array();
		$primaryCategory['categoryId'] = (string) $xml->PrimaryCategoryID;
		$primaryCategory['categoryName'] = (string) $xml->PrimaryCategoryName;
		
		$this->primaryCategory = (object) $primaryCategory;
		$this->productId = '';//$xml->;
		
		$secondaryCategory = array();
		$secondaryCategory['categoryId'] = (string) $xml->SecondaryCategoryID;
		$secondaryCategory['categoryName'] = (string) $xml->SecondaryCategoryName;
		
		$this->secondaryCategory = (object) $secondaryCategory;
		
		$sellerInfo = array();
		$sellerInfo['feedbackRatingStar'] = (string) $xml->Seller->FeedbackRatingStar;
		$sellerInfo['feedbackScore'] = (string) $xml->Seller->FeedbackScore;
		$sellerInfo['positiveFeedbackPercent'] = (string) $xml->Seller->PositiveFeedbackPercent;
		$sellerInfo['sellerUserName'] = (string) $xml->Seller->UserID;
		$sellerInfo['topRatedSeller'] = (boolean) $xml->Seller->TopRatedSeller;
		
		$this->sellerInfo = (object) $sellerInfo;
		
		$sellingStatus = array();
		$sellingStatus['bidCount'] = (string) $xml->BidCount;
		$sellingStatus['minimumToBid'] = (string) $xml->MinimumToBid;
		$sellingStatus['convertedCurrentPrice'] = (string) $xml->ConvertedCurrentPrice;
        $sellingStatus['convertedCurrentPrice_currencyId'] = (string) $xml->ConvertedCurrentPrice->attributes()->currencyID;

		$sellingStatus['currentPrice'] = (string) $xml->CurrentPrice;
        $sellingStatus['currentPrice_currencyId'] = (string) $xml->CurrentPrice->attributes()->currencyID;
		$sellingStatus['sellingState'] = (string) $xml->ListingStatus;
		$sellingStatus['timeLeft'] = (string) $xml->TimeLeft;

		
		
		$this->sellingStatus = (object) $sellingStatus;
    
		$shippingInfo = array();
		$shippingInfo['shippingServiceCost'] = (string) $xml->ShippingCostSummary->ShippingServiceCost;
		$shippingInfo['shippingType'] = (string) $xml->ShippingCostSummary->ShippingType;
		$shippingInfo['shipToLocations'] = explode(",", (string) $xml->ShipToLocations);
		
		$this->shippingInfo = (object) $shippingInfo;
 		
		$this->storeInfo = '';
		$this->subtitle = '';
		$this->title = (string) $xml->Title;
		$this->viewItemURL = (string) $xml->ViewItemURLForNaturalSearch;
		
	}
	
	protected function _init() 
	{
		
	}
	
}