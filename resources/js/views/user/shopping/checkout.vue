<template>
  <div class="app-container">
    <div class="flex justify-center">
      <el-steps :active="step" class="w-2/3" finish-status="success" align-center :process-status="processStatus" style="margin-top: 20px;">
        <el-step title="Items" @click="step==0" />
        <el-step title="Delivery" />
        <el-step title="Confirmation" />
      </el-steps>
    </div>
    <el-row v-if="step==0" :gutter="10" style="margin-top: 20px;">
      <el-col :xs="24" :sm="24" :md="16" :lg="16" :xl="16">
        <div class="shopping-cart rounded-lg shadow">
          <div class="title">
            Order Items
          </div>
          <div class="item" v-for="product in cartProducts" :key="product.id" v-if="cartProducts">
            <div class="pt-5 ml-1 mr-8 text-center lg:text-left">
              <el-button type="danger" icon="el-icon-delete" circle size="mini" :loading="buttonLoading" @click="removeFromCart(product.variant_id)"></el-button>
            </div>
            <div class="image" v-lazy-container="{ selector: 'img' }" style="min-width: 60px;max-width: 60px;">
              <img :data-src="product.products.cover_image_thumbnail" data-loading="images/fallback-product.png" alt="" style="max-height: 50px;max-width: 50px;" class="mt-2 rounded-md " />
            </div>
            <div class="description  ml-5 ">
              <div class="text-gray-700 font-bold text-xs mt-0 ">{{ product.products.name }}</div>
              <div class="text-gray-500 font-bold text-sm  ">{{product.products.qty}} {{product.products.qty_unit}}, {{ (product.variant.color?product.variant.color.name:' ') +' '+ (product.variant.size?'('+product.variant.size.brand_size+')':'') }}</div>
            </div>
            <div class="quantity">
              <div class="text-gray-600 font-bold text-md mt-3 ">{{ product.qty }} Units</div>
            </div>
            <div class="total-price font-extrabold text-gray-700"> {{product.products.dp_amount*product.qty | convert_with_symbol }}</div>
          </div>
        </div>
      </el-col>
      <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
        <div class="shopping-cart rounded-lg shadow-md">
          <div class="flex flex-col items-center w-full ">
            <div class="flex items-center justify-center p-3 mt-4 bg-green-100 rounded-full "><i class="fas fa-cart-arrow-down text-green-600"></i></div>
            <div class="text-lg font-bold text-green-700 leading-tight text-center mt-4 ">Checkout</div>
            <div class="text-sm text-center text-gray-700 text-secondary ">Verify the summary before purchase</div>
          </div>
          <div class="calculations mt-4">
            <div class="cal-grand">
              <span>Subtotal</span>
            </div>
            <div class="cal-amount"><span>{{temp.subtotal | convert_with_symbol}}</span></div>
          </div>
          <div class="calculations">
            <div class="cal-title">
              <span>IGST</span>
            </div>
            <div class="cal-amount"><span>{{temp.gst_amount | convert_with_symbol}}</span></div>
          </div>
          <div class="calculations">
            <div class="cal-title">
              <span>SGST</span>
            </div>
            <div class="cal-amount"><span>{{temp.sgst_amount | convert_with_symbol}}</span></div>
          </div>
          <div class="calculations">
            <div class="cal-title">
              <span>CGST</span>
            </div>
            <div class="cal-amount"><span>{{temp.cgst_amount | convert_with_symbol}}</span></div>
          </div>
          <div class="calculations">
            <div class="cal-title">
              <span>Shipping</span>
            </div>
            <div class="cal-amount"><span>{{temp.shipping | convert_with_symbol}}</span></div>
          </div>
          <div class="calculations">
            <div class="cal-title">
              <span>Distributor Discount</span>
            </div>
            <div class="cal-amount"><span>{{temp.distributor_discount | convert_with_symbol}}</span></div>
          </div>
          <div class="calculations">
            <div class="cal-title">
              <span>Total PV</span>
            </div>
            <div class="cal-amount"><span>{{temp.pv}}</span></div>
          </div>
          <div class="calculations">
            <div class="cal-grand">
              <span>Grand Total</span>
            </div>
            <div class="cal-amount"><span>{{Math.round(temp.grand_total) | convert_with_symbol}}</span></div>
          </div>
          <div class="checkout-btn make-payment-btn">
            <el-button class="checkout" type="success" round size="large" icon="el-icon-shopping-cart-full" :loading="buttonLoading" @click="gotoStep1()">Place Order</el-button>
          </div>
        </div>
      </el-col>
    </el-row>
    <el-row v-if="step==1" :gutter="20" style="margin-top: 20px;">
      <div class="shopping-cart rounded-lg shadow p-5">
        <div class="filter-container">
          <el-button class="filter-item" style="margin-left: 10px;" type="warning" @click="step=0"><i class="fas fa-arrow-left" /> Back</el-button>
          <el-button class="filter-item" style="margin-right: 10px;float: right;" type="success" @click="handleCreate"><i class="fas fa-plus" /> Add Address</el-button>
        </div>
        <el-form ref="orderForm" :rules="orderRules" :model="temp">
          <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">

              <el-form-item label="GSTIN" prop="gstin">
                <el-input v-model="temp.gstin" />
              </el-form-item>
            <el-form-item label="Shipping Address" prop="shipping_address_id">
              <el-select v-model="temp.shipping_address_id" style="width: 100%" clearable autocomplete="off" filterable placeholder="Select Shipping Address" @change="selectShippingAddress()" @clear="resetShippingAddress()">
                <el-option v-for="item in addresses" :key="item.id" :label="item.full_name" :value="item.id" />
              </el-select>
            </el-form-item>
            <el-form-item label="Full Name" prop="full_name">
              <el-input v-model="shipping_address.full_name" disabled />
            </el-form-item>
            <el-form-item label="Address" prop="address">
              <el-input v-model="shipping_address.address" type="textarea" disabled :rows="2" placeholder="Address" />
            </el-form-item>
            <el-form-item label="Lanmark" prop="landmark">
              <el-input v-model="shipping_address.landmark" disabled />
            </el-form-item>
            <el-row :gutter="5">
              <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item label="Pincode" prop="pincode">
                  <el-input v-model="shipping_address.pincode" disabled />
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item label="Mobile Number" prop="mobile_number">
                  <el-input v-model="shipping_address.mobile_number" disabled />
                </el-form-item>
              </el-col>
            </el-row>
            <el-row :gutter="5">
              <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item label="City" prop="city">
                  <el-input v-model="shipping_address.city" disabled />
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item label="State" prop="state">
                  <el-input v-model="shipping_address.state" disabled />
                </el-form-item>
              </el-col>
            </el-row>
            <el-form-item label="Country" prop="country">
              <el-input v-model="shipping_address.country" disabled />
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
            <br>
            <el-checkbox v-model="billing_address_tick" @change="selectSameBillingAddress()">Same as shipping address</el-checkbox>
            <br>
            <br>
            <el-form-item label="Billing Address" prop="billing_address_id">
              <el-select v-model="temp.billing_address_id" style="width: 100%" clearable autocomplete="off" filterable placeholder="Select billing Address" @clear="resetBillingAddress()" @change="selectBillingAddress()">
                <el-option v-for="item in addresses" :key="item.full_name" :label="item.full_name" :value="item.id" />
              </el-select>
            </el-form-item>
            <el-form-item label="Full Name" prop="full_name">
              <el-input v-model="billing_address.full_name" disabled />
            </el-form-item>
            <el-form-item label="Address" prop="address">
              <el-input v-model="billing_address.address" type="textarea" disabled :rows="2" placeholder="Address" />
            </el-form-item>
            <el-form-item label="Lanmark" prop="landmark">
              <el-input v-model="billing_address.landmark" disabled />
            </el-form-item>
            <el-row :gutter="5">
              <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item label="Pincode" prop="pincode">
                  <el-input v-model="billing_address.pincode" disabled />
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item label="Mobile Number" prop="mobile_number">
                  <el-input v-model="billing_address.mobile_number" disabled />
                </el-form-item>
              </el-col>
            </el-row>
            <el-row :gutter="5">
              <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item label="City" prop="city">
                  <el-input v-model="billing_address.city" disabled />
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item label="State" prop="state">
                  <el-input v-model="billing_address.state" disabled />
                </el-form-item>
              </el-col>
            </el-row>

            <el-form-item label="Country" prop="country">
              <el-input v-model="billing_address.country" disabled />
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
            <div class="shopping-cart" style="margin-top: 20px;">
              <div class="calculations">
                <div class="cal-grand">
                  <span>Wallet Balance</span>
                </div>
                <div class="cal-amount"><span>{{ balance | convert_with_symbol }}</span></div>
              </div>
              <div class="calculations">
                <div class="cal-grand">
                  <span>Amount Payable</span>
                </div>
                <div class="cal-amount"><span>{{ temp.grand_total | convert_with_symbol }}</span></div>
              </div>
              <div class="">
                <div class="payment-mode">
                  <el-form-item label="" prop="payment_mode">
                    <el-select v-model="temp.payment_mode" style="width: 100%" autocomplete="off" filterable placeholder="Payment Mode">
                      <el-option v-for="item in payment_modes" :key="item.name" :label="item.name" :value="item.name" :disabled="item.disabled?item.disabled:false" />
                    </el-select>
                  </el-form-item>
                </div>
              </div>
              <div class="make-payment-btn">
                <el-button class="checkout" type="success" round size="large" icon="el-icon-shopping-cart-full" :loading="buttonLoading" @click="placeOrder()">Make Payment</el-button>
              </div>
            </div>
          </el-col>
        </el-form>
      </div>
    </el-row>
    <el-dialog title="Add Address" width="60%" top="30px" :fullscreen="is_mobile" :visible.sync="dialogAddressesVisible">
      <el-form ref="AddressData" :rules="rulesAddress" :model="tempAddress" style="">
        <el-row :gutter="10">
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <el-form-item label="Full Name" prop="full_name">
              <el-input v-model="tempAddress.full_name" />
            </el-form-item>
          <!--   <el-form-item label="Mobile Number" prop="mobile_number">
              <el-input v-model="tempAddress.mobile_number" />
            </el-form-item> -->
              <el-form-item label="Mobile Number" prop="mobile_number">
                <el-input  v-model="tempAddress.mobile_number" type="text" auto-complete="on" placeholder="Enter valid Mobile No." >
                    <el-select v-model="tempAddress.country_code" class="countryFlag" slot="prepend" placeholder="Country" filterable  prop="country_code" style="width: 110px !important;">
                            <el-option
                              v-for="item in Country"
                              :key="item.city_country"
                              :label="item.phonecode+'  '+item.country_img"
                              :value="item.phonecode" >
                              <span style="float: left">{{ item.phonecode }}</span>
                              <span style="float: right; color: #8492a6; font-size: 13px">{{ item.country_img }}</span>
                            </el-option>
                    </el-select>
                </el-input>
            </el-form-item>

            <el-form-item label="Default ?" prop="is_default">
              <br>
              <el-checkbox v-model="tempAddress.is_default" label="Default Address ?" border></el-checkbox>
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <el-form-item label="Address" prop="address">
              <el-input v-model="tempAddress.address" />
            </el-form-item>
            <el-form-item label="Landmark" prop="landmark">
              <el-input v-model="tempAddress.landmark" />
            </el-form-item>
          
             <el-form-item label="Country" prop="country">
                <el-select v-model="tempAddress.country" style="width: 100%" filterable @change="handleCountryChange" placeholder="Select Country">
                    <el-option
                      v-for="item in Country"
                      :key="item.country_img"
                      :label="item.city_country"
                      :value="item.city_country">
                      <span style="float: left">{{ item.city_country }}</span>
                      <span style="float: right; color: #8492a6; font-size: 13px">{{ item.country_img }}</span>
                    </el-option>
                </el-select>
            </el-form-item>

          <el-form-item label="State" prop="state">
            <el-select v-model="tempAddress.state" style="width: 100%" filterable @change="handleStateChange" placeholder="Select Province/State">
              <el-option
                v-for="item in states"
                :key="item"
                :label="item"
                :value="item">
              </el-option>
            </el-select>
          </el-form-item>
            <el-form-item label="City" prop="city">
                <br>
                <el-select v-model="tempAddress.city"  style="width: 100%" filterable placeholder="Select City">
                    <el-option
                        v-for="item in cities"
                        :key="item"
                        :label="item"
                        :value="item">
                    </el-option>
                </el-select>
            </el-form-item>
       

            <el-form-item label="Pincode" prop="pincode">
              <el-input v-model="tempAddress.pincode" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button size="mini" @click="dialogAddressesVisible = false">
          Cancel
        </el-button>
        <el-button size="mini" type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="dialogStatus==='create'?createData():updateData()">
          Save
        </el-button>
      </div>
    </el-dialog>
    <el-row v-if="step==2" :gutter="10" style="margin-top: 20px;">
      <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
        <div class="order-success pb-16">
          <center>
            <h4 class="middel-text text-2xl mt-10 leading-8 mb-1 text-gray-700 font-bold px-3 md:px-0">Your order has been placed successfully</h4>
            <h1 style="text-align: center;" class="text-2xl mt-2 leading-8 mb-1 text-gray-800 font-bold">Order No #{{ order_no }}</h1>
            <img :src="orderSuccess" alt="" style="max-height: 350px;max-width: 350px;">
          </center>
        </div>
      </el-col>
    </el-row>
  </div>
</template>
<script>
import { getMyCart, addToCart, removeFromCart, updateCartQty, placeOrder } from '@/api/user/shopping';
import { getProfile } from "@/api/user/members";
import { getSettings } from '@/api/user/settings';
import { getCurrencies,getAllCountry,getCountryStates ,getStateCities, getPackages } from '@/api/user/config';
import { getAllAddresses, createAddress } from '@/api/user/addresses';
import { getMyBalance } from '@/api/user/wallet';
import waves from '@/directive/waves';
import { parseTime } from '@/utils';
import Pagination from '@/components/Pagination';
import orderSuccess from '@/assets/images/order-success.png';


export default {
  name: 'checkout',
  components: { Pagination },
  directives: { waves },
  data() {    
    const validateContact = (rule, value, callback) => {
      var pattern = /^\d*(?:\.\d{1,2})?$/;
      if (!pattern.test(value)) {
        callback(new Error('Enter valid contact number.'));
      } else {
        callback();
      }
    };
    return {
      is_mobile: false,
      temp: {
        subtotal: 0,
        gst_amount: 0,
        sgst_amount: 0,
        cgst_amount: 0,
        utgst_amount: 0,
        shipping: 0,
        discount: 0,
        grand_total: 0,
        gstin:undefined,
        pv: 0,
        distributor_discount: 0,
        shipping_address_id: undefined,
        billing_address_id: undefined,
      },
      tempAddress: {
        id: undefined,
        full_name: undefined,
        mobile_number: undefined,
        pincode: undefined,
        country_code: undefined,
        address: undefined,
        landmark: undefined,
        city: undefined,
        state: undefined,
        country: undefined,
        is_default: true
      },
      orderRules: {
        shipping_address_id: [{ required: true, message: 'Shipping Adddress is required', trigger: 'change' }],
        payment_mode: [{ required: true, message: 'Please select payment mode.', trigger: 'change' }],
      },

      rulesAddress: {
        full_name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        mobile_number: [{ required: true, validator: validateContact, trigger: 'blur' }],
        address: [{ required: true, message: 'Address is required', trigger: 'blur' }],
        city: [{ required: true, message: 'City is required', trigger: 'blur' }],
        state: [{ required: true, message: 'State is required', trigger: 'blur' }],
        country: [{ required: true, message: 'Country is required', trigger: 'blur' }],
      },
      billing_address: {
        full_name: undefined,
        mobile_number: undefined,
        pincode: undefined,
        address: undefined,
        landmark: undefined,
        city: undefined,
        state: undefined,
        country: undefined,
      },
      shipping_address: {
        full_name: undefined,
        mobile_number: undefined,
        pincode: undefined,
        address: undefined,
        landmark: undefined,
        city: undefined,
        state: undefined,
        country: undefined,
      },
      is_shipping_waiver: true,
      payment_modes: [{
          id: 1,
          name: 'Wallet',
        },
        // {
        //   id: 2,
        //   name: 'Online',
        //   disabled: true,
        // }
      ],
      categories: [],
      cartProducts: [],
      Country:[],
      states:[],
      cities:[],
      settings: {
        shipping_charge: 0,
        shipping_charge_2: 0,
      },
      billing_address_tick: false,
      orderSuccess: orderSuccess,
      processStatus: 'process',
      dialogAddressesVisible: false,
      step: 0,
      addresses: [],
      order_no: undefined,
      balance: 0.00,
      is_create: true,
      downloadLoading: false,
      buttonLoading: false,

    };
  },
  created() {
    this.getSettings();
    this.getMyCart();
    this.getAllAddresses();
    this.getProfile();
    this.getMyBalance();
    if (window.screen.width <= '550') {
      this.is_mobile = true;
    }
     getAllCountry().then(response => {
        this.Country = response.data;
    });
  },
  methods: {
    getProfile() {
      getProfile().then(response => {
        this.profile = response.data
        let tempProfile=Object.assign({}, this.profile );
        this.temp.gstin = tempProfile.gstin;
      });
    },
    handleStateChange(){
        this.tempAddress.city = undefined;
        this.cities = [];
        getStateCities(this.tempAddress.state).then(response => {
            this.cities = response.data;
        });
    },
    handleCountryChange(){
        this.tempAddress.city = undefined;
        this.tempAddress.state = undefined;
        getCountryStates(this.tempAddress.country).then(response => {
            this.states = response.data;
        });
    },
    createData() {
      this.$refs["AddressData"].validate(valid => {
        if (valid) {
          this.buttonLoading = true;
          createAddress(this.tempAddress).then((data) => {
            this.getAllAddresses();
            this.dialogAddressesVisible = false;
            this.buttonLoading = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading = false;
            this.resetTempAddress();
          }).catch((err) => {
            this.buttonLoading = false;
          });
        }
      });
    },
    handleCreate() {
      this.resetTempAddress();
      this.dialogStatus = "create";
      this.dialogTitle = "Add Address";
      this.getSettings();
      this.dialogAddressesVisible = true;
    },
    resetTempAddress() {
      this.tempAddress = {
        id: undefined,
        full_name: undefined,
        mobile_number: undefined,
        country_code: undefined,
        pincode: undefined,
        address: undefined,
        landmark: undefined,
        city: undefined,
        state: undefined,
        country: undefined,
        is_default: true
      };
    },
    getMyCart() {
      getMyCart().then(response => {
        this.cartProducts = response.data;
        this.calculateFinal();
        if(!this.is_shipping_waiver){
          if(this.temp.grand_total < this.settings.shipping_criteria){
            this.temp.shipping=parseFloat(this.settings.shipping_charge_2);
          }else{
            this.temp.shipping=parseFloat(this.settings.shipping_charge);
          }
        }

        this.temp.grand_total+=this.temp.shipping;
        if (this.cartProducts.length == 0) {
          this.$router.push('/shopping/products');
        }
      });
    },
    getSettings() {
      getSettings().then(response => {
        this.settings = response.data;
        this.tempAddress.country_code  = response.data.default_country_code;

      });
    },
    resetBillingAddress() {
      this.billing_address = {
        full_name: undefined,
        mobile_number: undefined,
        pincode: undefined,
        address: undefined,
        landmark: undefined,
        city: undefined,
        state: undefined,
      };
    },
    resetShippingAddress() {
      this.shipping_address = {
        full_name: undefined,
        mobile_number: undefined,
        pincode: undefined,
        address: undefined,
        landmark: undefined,
        city: undefined,
        state: undefined,
      };
    },
    selectSameBillingAddress() {
      if (this.billing_address_tick) {
        const temp = Object.assign({}, this.temp);
        this.temp.billing_address_id = temp.shipping_address_id;
        this.selectBillingAddress();
      } else {
        this.temp.billing_address_id = undefined;
        this.resetBillingAddress();
      }
    },
    getAllAddresses() {
      getAllAddresses().then(response => {
        this.addresses = response.data;
      });
    },
    getMyBalance() {
      getMyBalance().then(response => {
        this.balance = response.data;
      });
    },
    selectShippingAddress() {
      this.temp.billing_address_id = undefined;
      this.resetBillingAddress();
      this.billing_address_tick = false;
      const address = this.addresses.filter((address) => {
        return address.id == this.temp.shipping_address_id;
      });
      if (address[0]) {
        this.shipping_address = address[0];
      }
    },
    selectBillingAddress() {
      const address = this.addresses.filter((address) => {
        return address.id == this.temp.billing_address_id;
      });
      if (address[0]) {
        this.billing_address = address[0];
      }
    },
    calculateFinal() {
      this.resetTemp();
      this.cartProducts.forEach((cart) => {
        this.temp.subtotal += parseFloat(cart.products.dp_base) * parseInt(cart.qty);
        this.temp.distributor_discount += parseFloat(cart.products.retail_amount - cart.products.dp_amount) * parseInt(cart.qty);
        if (this.user_state == this.settings.home_state) {
          this.temp.sgst_amount += parseFloat(cart.products.dp_sgst_amount) * parseInt(cart.qty);
          this.temp.cgst_amount += parseFloat(cart.products.dp_cgst_amount) * parseInt(cart.qty);
        } else {

          this.temp.gst_amount += (parseFloat(cart.products.dp_gst_amount) * parseInt(cart.qty));
        }

        this.temp.discount += parseFloat(cart.products.discount_amount) * parseInt(cart.qty);
        this.temp.pv += parseFloat(cart.products.pv) * parseInt(cart.qty);
        this.temp.grand_total = this.temp.subtotal + this.temp.gst_amount + this.temp.sgst_amount + this.temp.cgst_amount + this.temp.utgst_amount + this.temp.shipping - this.temp.discount;

        if(!cart.products.is_shipping_waiver){
            this.is_shipping_waiver=false
        }

      });
    },
    resetTemp() {
      this.temp = {
        subtotal: 0,
        gst_amount: 0,
        sgst_amount: 0,
        cgst_amount: 0,
        utgst_amount: 0,
        gstin:undefined,
        shipping: 0,
        discount: 0,
        grand_total: 0,
        pv: 0,
        distributor_discount: 0,
        shipping_address_id: undefined,
        billing_address_id: undefined,
      };
    },
    gotoStep1() {
      this.step = 1;
    },
    placeOrder() {
      this.$refs['orderForm'].validate(valid => {
        if (valid) {
          if (parseFloat(this.balance) < parseFloat(this.temp.grand_total)) {
            this.$message.error('Oops, You have not enough balance to place order.');
            return;
          }
          this.buttonLoading = true;
          placeOrder(this.temp).then((response) => {
            this.step = 2;
            this.processStatus = 'success';
            this.order_no = response.data.order_no;
            this.$events.fire('update-cart-count');
            this.$notify({
              title: 'Success',
              message: response.message,
              type: 'success',
              duration: 2000,
            });
            this.buttonLoading = false;
          }).catch((error) => {
            this.buttonLoading = false;
          });
        }
      });
    },
    updateCartQty(id, qty, stock, varinat_id) {
      let tempData = {
        'product_id': id,
        'qty': qty,
        'varinat_id': varinat_id,
      };
      updateCartQty(tempData).then((response) => {
        this.buttonLoading = false;
        this.getMyCart();
      }).catch((res) => {
        this.getMyCart();
        this.$notify({
          title: "Success",
          message: response.message,
          type: "success",
          duration: 2000
        });
      });
    },
    removeFromCart(id) {
      this.buttonLoading = true;
      removeFromCart(id).then((response) => {
        this.buttonLoading = false;
        this.getMyCart();
        this.is_shipping_waiver=true;
        this.$events.fire('update-cart-count');
        this.$notify({
          title: 'Success',
          message: response.message,
          type: 'success',
          duration: 2000,
        });
      }).catch((error) => {
        this.buttonLoading = false;
      });
    },
  },
};

</script>
<style scoped>
* {
  box-sizing: border-box;
}

.app-container {
  background-color: #f1f5f9;
}

html,
body {
  width: 100%;
  height: 100%;
  padding: 10px;
  margin: 0;
  background-color: #f1f5f9 !important;
  font-family: 'Roboto', sans-serif;
}

.shopping-cart {

  background: #FFFFFF;
  /*  box-shadow: 0px 1px 10px 5px rgba(0,0,0,0.10);
  border-radius: 6px;*/
  display: flex;
  flex-direction: column;
}

.order-success {
  margin: 0 auto;
  width: 100%;
}

.title {
  height: 60px;
  padding: 20px 30px;
  color: #5E6977;
  font-size: 18px;
  font-weight: 400;
}

.calculations {
  padding: 0px 7px;
  height: 30px;
  display: flex;
}

.calculations {
  /* border-top:  1px solid #E1E8EE;*/
  /*border-bottom: 1px solid #E1E8EE;*/
}

.item {
  padding: 0px 15px;
  height: 65px;
  display: flex;
}

.item {
  /*border-top:  1px solid #E1E8EE;*/
  border-bottom: 1px solid #E1E8EE;
}

/* Buttons -  Delete and Like */
.buttons {
  position: relative;
  padding-top: 12px;
  margin-right: 60px;
}

.delete-btn {
  display: inline-block;
  cursor: pointer;
  width: 18px;
  height: 17px;

  margin-right: 20px;
}

.like-btn {
  position: absolute;
  top: 9px;
  left: 15px;
  display: inline-block;

  width: 60px;
  height: 60px;
  background-size: 2900%;
  background-repeat: no-repeat;
  cursor: pointer;
}

.is-active {
  animation-name: animate;
  animation-duration: .8s;
  animation-iteration-count: 1;
  animation-timing-function: steps(28);
  animation-fill-mode: forwards;
}

@keyframes animate {
  0% {
    background-position: left;
  }

  50% {
    background-position: right;
  }

  100% {
    background-position: right;
  }
}


/* Product Description */
.description {
  padding-top: 10px;
  margin-right: 60px;
  width: 200px;
}

.description span {
  display: block;
  font-size: 14px;
  color: #43484D;
  font-weight: 400;
}

.description span:first-child {
  margin-bottom: 5px;
}

.description span:last-child {
  font-weight: 300;
  margin-top: 8px;
  color: #86939E;
}

.cal-title {
  width: 100%;
}

.cal-title span {
  margin-left: 25px;
  display: block;
  font-size: 16px;
  color: #6c7175;
  font-weight: 400;
}

.cal-grand {
  width: 100%;
}

.cal-grand span {
  margin-left: 25px;
  display: block;
  font-size: 16px;
  margin-top: 0px;
  color: #5d5d5d;
  font-weight: 600;
}

.payment-mode-div {
  height: 55px;
  border-bottom: 1px solid #E1E8EE;
}

.payment-mode {
  margin: 10px 20px 10px 20px;
}

.cal-title span:first-child {
  margin-bottom: 2px;
}

.cal-title span:last-child {
  font-weight: 300;
  margin-top: 2px;
  color: #86939E;
}

/* Product Quantity */
.quantity {
  padding-top: 10px;
  margin-right: 10px;
}

.quantity input {
  -webkit-appearance: none;
  border: none;
  text-align: center;
  width: 32px;
  font-size: 16px;
  color: #43484D;
  font-weight: 300;
}

.checkout-btn {
  padding: 15px 15px 15px 15px;
}

.make-payment-btn {
  padding: 15px 15px 15px 15px;
  margin: 0 auto;
}

.checkout-btn button {
  float: right;
}

button[class*=btn] {
  width: 30px;
  height: 30px;
  background-color: #E1E8EE;
  border-radius: 6px;
  border: none;
  cursor: pointer;
}

.minus-btn img {
  margin-bottom: 3px;
}

.plus-btn img {
  margin-top: 2px;
}

button:focus,
input:focus {
  outline: 0;
}

/* Total Price */
.total-price {
  width: 150px;
  padding-top: 20px;
  text-align: center;
  font-size: 16px;
}

.cal-amount {
  width: 70%;
  margin-right: 25px;
  padding-top: 0px;
  text-align: right;
  font-size: 16px;
  color: #43484D;
  font-weight: 600;
}

/* Responsive */
@media (max-width: 800px) {
  .shopping-cart {
    width: 100%;
    height: auto;
    overflow: hidden;
  }

  .item {
    height: auto;
    flex-wrap: wrap;
    justify-content: center;
  }

  .image img {
    max-height: 50px;
    max-width: 50px;
  }

  .image,
  .quantity,
  .description {
    width: 100%;
    text-align: center;
    margin: 6px 0;
  }

  .buttons {
    margin-right: 20px;
  }
}

.el-form-item--medium .el-form-item__content,
.el-form-item--medium .el-form-item__label {
  line-height: 30px;
}

.el-form-item {
  margin-bottom: 10px;
}

</style>
