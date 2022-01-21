Stripe Subscription: 

Introducing the newest offering from Team Digital Tricksters: Stripe Payment!

Now avail of the much-awaited subscription functionality while enrolling students in Moodle courses using  the Stripe payment gateway for paid courses with Strong customer authentication (SCA).

This plugin will help the admins and webmasters to offer their students a  monthly or yearly subscription option for the paid courses.


Stripe Payment:

1. Registered users can log in to the Moodle site and happily apply the promo codes for a discount before payment.
2. They can subscribe to a monthly or a yearly plan based on the settings applied by the admin. On successful payment, they can access the course.
3. Admins and Webmasters, now, can create, manage, and keep track of all promotional codes directly in their Stripe dashboard.
4. Strong customer authentication (SCA) implemented with 4 layers of complex security to comply with the EU Revised Directive on Payment Services (PSD2) on payment service providers within the European Economic Area.
5. The first of it's kind to use the Payment intent method for Stripe-coupon. 
6. Works with all stable versions of Moodle till v 3.10
7. Latest Stripe SDK

Stripe Payment Documentation:

This plugin has all the settings for development as well as for production usage. It's easy to install, set up, and effective.

Creating a Merchant Account :

1) Create an account at https://stripe.com.
2) Complete your merchant profile details from https://dashboard.stripe.com/account.
3) Now set up the secret key and publisher's key at https://dashboard.stripe.com/account/apikeys.
4) For test mode use test API keys and for live mode use live API keys.

Now you are done with the merchant account set up.

Installation Guidance : 

Upload and install via ZIP file
1.	Login to your Moodle site as an admin.
2.	Go to Administration > Site administration > Plugins > Install plugins.
3.	In your downloaded package from CodeCanyon, open the folder Plugin.
4.	You will see the  Zip files there.
5.	Please Choose the stripesubscription zip 
6.	Check that you obtain a 'Validation passed!' message, then click the button 'Install plugin'.
 
During the next steps the Moodle database will be upgraded. Though you can already set up the theme options at this stage, it is recommended to leave the theme options at the default settings at first and just klick Save Changes to continue.

Installing manually at the server
If you can't deploy the plugin code via the administration web interface, you have to copy it to the server file system manually (e.g. if the web server process does not have write access to the Moodle installation tree to do this for you).
For Main file,
your destination will be:
/path/to/moodle/enrol/

1. Unzip it 
2. In your Moodle site (as admin) go to dashboard, you will get the installation prompt.

Then the process is the same as "Upload and install via ZIP file"
.

Go to Site administration > Plugins > Enrolments > Manage enrolment plugin
 
Enable 'Stripe Subscription’' from the list

3) Click 'Settings' which will lead to the settings page of the plugin

4) Provide merchant credentials for Stripe. Note that, you will get all the details from your merchant account. 
 
Now select the checkbox as per requirement. 
Now please check the global course settings which will be applied to every course which uses this payment method if the course price is not set for a specific course.
 

5) Select any course from the course listing page.

6) Go to Course administration > Users > Enrolment methods > Add method 'Stripe' from the dropdown. 
Set 'Custom instance name', 'Enrol cost' etc and add the method.
Setting a price for your course
1.	In Course Administration > Users > Enrolment methods, click the edit/hand/pen icon to the right of the Stripe Subscription option.
2.	Optional: Give a name to this enrolment method if you wish in "Custom Instance name"
3.	Ensure that "Allow Stripe Subscription  enrolments" is set to "yes"
4.	In "Enrol cost", type in the cost of your course and in "Currency" choose your currency.
5.	Usually you would leave the "Assign role" as "student" unless you have a very special reason for allowing your users to enrol as, say, editing teachers etc
6.	If you want to limit the number of users that can be enrolled using this, please put a numerical value in the ‘Max Enrolled Users’ field.
7.	Choose an enrolment period and/or start/end dates if desired.
8.	Select if it is a monthly or a yearly subscription using the subscription interval dropdown.
9.	If you want to offer trial usage , put a numerical value for the duration in days.
10.	 Select if Coupon will be available during checkout or not using the Stripe coupon dropdown.
11.	Click the "Save changes" button.
 

Adding coupons:

Go to your Stripe Dashboard >  Product > Coupons > Create a coupon.

Type in the coupon’s name: it can be anything and for your reference only.
Type in the Coupon’s ID: This is the Coupon code that your students will need to enter if they want to avail of the discount.

Choose Coupon Type: 
1. Percentage discount: offers % off on the course price 
2. Fixed amount discount: Offers a fixed amount off on the course price.

Duration: For the duration, when using the value repeating, also specify the duration in months 
as the number of months for which the coupon should repeatedly apply. 
Otherwise, the coupon can be set to apply only to a single invoice or to them all.

Redemption: The max_redemptions and redeem_by values apply to the coupon across every customer you have. 
For example, you can restrict a coupon to the first 50 customers that use it, or you can make a coupon expire by a certain date. 
If you do the latter, this only impacts when the coupon can be applied to a customer. 
If you set a coupon to last forever when used by a customer, but have it expire on January 1st, 
any customer is given that coupon will have that coupon’s discount forever, 
but no new customers can apply the coupon after January 1st.

If a coupon has a max_redemptions value of 50, it can only be applied among all your customers a total of 50 times, 
although there’s nothing preventing a single customer from using it multiple times. 
(You can always use logic on your end to prevent that from occurring.)

This completes all the steps from the administrator end. 
Now registered users can log in to the Moodle site and view the course 
after successful payment of the discounted price.

Please add a menu to view the subscription status:
 

Stripe Subscription Status|/enrol/stripesubscription/subscriptions.php
 


