<template>
  <div v-bind:class="[step == 0 ? 'app-container' : '']">
     <div class="filter-container" v-if="step == 0">
      <el-button v-waves class="filter-item" type="success" icon="el-icon-back" @click="$router.push('/shopping/combos')">Go to All Combos</el-button>
      <!-- <el-button v-waves class="filter-item" type="success" icon="el-icon-shopping-cart-full" @click="$router.push('/shopping/cart')" style="float: right;">Go to Cart</el-button> -->
    </div>
    <el-row :gutter="10" style="height:100%" v-if="step == 0">
      <el-col
        :xs="24"
        :sm="24"
        :md="16"
        :lg="16"
        :xl="16"
        style="padding:0rem 1rem; display:flex; flex-direction:column; border-right:1px solid #ededed; height:100%"
      >
        <div>
          <h2 class="font-bold uppercase text-gray-500 mb-2">
            Product Combo Order : Combo # {{ combo.name }}
          </h2>
        </div>
        <el-row
          id="productsScroll"
          style="max-height:calc(100% - 52px); min-height:calc(100% - 52px); overflow:auto"
        >
          <div
            v-for="comboCategory in combo.categories"
            :key="comboCategory.id"
            v-if="comboCategory"
          >
            <el-card
              class="box-card rounded-lg mb-2"
              :body-style="{ padding: '0.5rem' }"
              shadow="never"
              v-for="quantity in comboCategory.quantity"
              :key="quantity"
            >
              <div>
                <span class="m-0 p-0 text-sm"
                  >{{ comboCategory.category.name }} {{ quantity }}</span
                >
              </div>
              <el-row class="mt-2 mb-2">
                  <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                  <label class="text-gray-700 text-sm" for="count"
                    >Select Size :
                  </label>
                  <br/>
                  <el-select
                    size="mini"
                    v-model="
                      selectedCategorySizes[
                        comboCategory.category_id + '_' + quantity
                      ]
                    "
                    @change="
                      getProductsBySizeAndColor(
                        comboCategory.category_id,
                        comboCategory.category_id + '_' + quantity
                      )
                    "
                    clearable
                    class="filter-item "
                    style="width:200px;"
                    filterable
                    placeholder="Select size"
                  >
                    <el-option
                      v-for="item in comboCategorySizes[
                        comboCategory.category_id + '_' + quantity
                      ]"
                      :key="item.name"
                      :label="item.name"
                      :value="item.id"
                    >
                    </el-option>
                  </el-select>
                </el-col>
                <!-- <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                  <label class="text-gray-700 text-sm" for="count"
                    >Select Color :
                  </label>
                  <el-select
                    size="mini"
                    v-model="
                      selectedCategoryColors[
                        comboCategory.category_id + '_' + quantity
                      ]
                    "
                    @change="
                      getProductsBySizeAndColor(
                        comboCategory.category_id,
                        comboCategory.category_id + '_' + quantity
                      )
                    "
                    clearable
                    class="filter-item "
                    style="width:200px;"
                    filterable
                    placeholder="Select color"
                  >
                    <el-option
                      v-for="item in comboCategoryColors[
                        comboCategory.category_id + '_' + quantity
                      ]"
                      :key="item.name"
                      :label="item.name"
                      :value="item.id"
                    >
                    </el-option>
                  </el-select>
                </el-col> -->
              
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                  <label class="text-gray-700 text-sm" for="count"
                    >Select Product :
                  </label>
                  <br/>
                  <el-select
                    size="mini"
                    v-model="
                      selectedCategoryProducts[
                        comboCategory.category_id + '_' + quantity
                      ]
                    "
                    clearable
                    class="filter-item "
                    style="width:280px;"
                    filterable
                    placeholder="Select product"
                     @change="
                      getProduct(
                        comboCategory.category_id + '_' + quantity
                      )
                    " value-key="id"
                  >
                    <el-option
                      v-for="item in comboCategoryProducts[
                        comboCategory.category_id + '_' + quantity
                      ]"
                      :key="item.id"
                      :label="item.product.name"
                      :value="item"
                      
                    >
                    <img style="max-width:32px;display: inline-block;margin-right: 8px;" :src="item.product.cover_image_thumbnail"><span>{{ item.product.name }}</span>
                    </el-option>
                  </el-select>
                </el-col>
              </el-row>

              <el-row
                :gutter="10"
                v-if="
                  product[
                    comboCategory.category_id + '_' + quantity
                  ]
                "
              >
                <el-col>
                  <div class="shopping-cart rounded-lg shadow">
                    <div class="item flex flex-col md:flex-row h-full md:h-128">
                      <div
                        class="image flex justify-center w-auto"
                        v-lazy-container="{ selector: 'img' }"
                      >
                        <img
                          :data-src="
                            product[
                              comboCategory.category_id + '_' + quantity
                            ].cover_image_thumbnail ||
                              product[
                                comboCategory.category_id + '_' + quantity
                              ].cover_image
                          "
                          data-loading="images/fallback-product.png"
                          alt=""
                          class="max-h-128 md:max-h-48"
                        />
                      </div>
                      <div class="description">
                        <div class="text-gray-700 font-bold text-sm mt-3 ">
                          {{
                            product[
                              comboCategory.category_id + '_' + quantity
                            ].name
                          }}
                        </div>
                        <div class="text-gray-500 font-bold text-sm  ">
                          {{
                            product[
                              comboCategory.category_id + '_' + quantity
                            ].qty
                          }}
                          {{
                            product[
                              comboCategory.category_id + '_' + quantity
                            ].qty_unit
                          }}
                        </div>
                      </div>
                      <div class="description-base">
                        <span class="text-gray-700 mt-3 "
                          >DP - ₹ {{ comboCategory.dp_amount }} /-
                          <el-popover
                            placement="right"
                            width="200"
                            trigger="hover"
                          >
                            <span
                              class="product-brand"
                              slot="reference"
                              style="font-weight: normal;text-decoration: underline;"
                              ><a>Inclusive of all taxes</a></span
                            >
                            <div style="margin-left: 5px;">
                              <p>
                                <b>Base price</b> : {{ comboCategory.dp_base }}
                              </p>
                              <p>
                                <b>GST</b> : {{ comboCategory.dp_gst_amount }}
                              </p>
                              <p><b>DP</b> : {{ comboCategory.dp_amount }}</p>
                            </div>
                          </el-popover>
                        </span>
                        <div></div>
                      </div>
                    </div>
                  </div>
                </el-col>
              </el-row>
            </el-card>
          </div>
        </el-row>
      </el-col>
      <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
        <div class="shopping-cart rounded-lg shadow" v-if="combo.length != 0">
          <div class="flex flex-col items-center w-full ">
            <div
              class="flex items-center justify-center p-3 mt-4 bg-green-100 rounded-full "
            >
              <i class="fas fa-cart-arrow-down text-green-600"></i>
            </div>
            <div
              class="text-lg font-bold text-green-700 leading-tight text-center mt-4 "
            >
              Your Cart
            </div>
            <!-- <div class="text-sm text-center text-gray-700 text-secondary ">Verify the summary before purchase</div> -->
          </div>
          <div class="calculations mt-4">
            <div class="cal-grand">
              <span>Subtotal</span>
            </div>
            <div class="cal-amount">
              <span>{{ combo.base_amount | convert_with_symbol }}</span>
            </div>
          </div>
          <div class="calculations">
            <div class="cal-title">
              <span>GST</span>
            </div>
            <div class="cal-amount">
              <span>{{ combo.gst_amount | convert_with_symbol }}</span>
            </div>
          </div>
          <!-- <div class="calculations">
                    <div class="cal-title">
                        <span>SGST</span>
                    </div>
                    <div class="cal-amount"><span>{{combo.sgst_amount | convert_with_symbol}}</span></div>
                </div>
                <div class="calculations">
                    <div class="cal-title">
                        <span>CGST</span>
                    </div>
                    <div class="cal-amount"><span>{{combo.cgst_amount | convert_with_symbol}}</span></div>
                </div> -->
          <div class="calculations">
              <div class="cal-title">
                <span>Shipping</span>
              </div>
              <div class="cal-amount"><span>{{temp.shipping | convert_with_symbol}}</span></div>
            </div>
          <div class="calculations">
            <div class="cal-title">
              <span>Total PV</span>
            </div>
            <div class="cal-amount">
              <span>{{ combo.pv }}</span>
            </div>
          </div>
          <div class="calculations">
            <div class="cal-grand">
              <span>Grand Total</span>
            </div>
            <div class="cal-amount">
              <span>{{ temp.grand_total | convert_with_symbol }}</span>
            </div>
          </div>
          <div class="checkout-btn make-payment-btn">
            <el-button
              class="checkout"
              type="success"
              round
              size="large"
              icon="el-icon-shopping-cart-full"
              :loading="buttonLoading"
              @click="gotoStep1()"
              >Place Order</el-button
            >
          </div>
        </div>
      </el-col>
    </el-row>
    <el-row v-if="step == 1" :gutter="20" style="margin-top: 20px;">
      <div class="shopping-cart rounded-lg shadow p-5">
        <div class="filter-container">
          <el-button
            class="filter-item"
            style="margin-left: 10px;"
            type="warning"
            @click="step = 0"
            ><i class="fas fa-arrow-left" /> Back</el-button
          >
          <el-button
            class="filter-item"
            style="margin-right: 10px;float: right;"
            type="success"
            @click="handleCreate"
            ><i class="fas fa-plus" /> Add Address</el-button
          >
        </div>
        <el-form ref="orderForm" :rules="orderRules" :model="temp">
          <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
            <el-form-item label="GSTIN" prop="gstin">
              <el-input v-model="temp.gstin" />
            </el-form-item>
            <el-form-item label="Shipping Address" prop="shipping_address_id">
              <el-select
                v-model="temp.shipping_address_id"
                style="width: 100%"
                clearable
                autocomplete="off"
                filterable
                placeholder="Select Shipping Address"
                @change="selectShippingAddress()"
                @clear="resetShippingAddress()"
              >
                <el-option
                  v-for="item in addresses"
                  :key="item.id"
                  :label="item.full_name"
                  :value="item.id"
                />
              </el-select>
            </el-form-item>
            <el-form-item label="Door No / Flat No" prop="door_no">
              <el-input v-model="shipping_address.door_no" disabled />
            </el-form-item>
            <el-form-item label="Full Name" prop="full_name">
              <el-input v-model="shipping_address.full_name" disabled />
            </el-form-item>
            <el-form-item label="Address" prop="address">
              <el-input
                v-model="shipping_address.address"
                type="textarea"
                disabled
                :rows="2"
                placeholder="Address"
              />
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
            <br />
            <el-checkbox
              v-model="billing_address_tick"
              @change="selectSameBillingAddress()"
              >Same as shipping address</el-checkbox
            >
            <br />
            <br />

            <el-form-item label="Billing Address" prop="billing_address_id">
              <el-select
                v-model="temp.billing_address_id"
                style="width: 100%"
                clearable
                autocomplete="off"
                filterable
                placeholder="Select billing Address"
                @clear="resetBillingAddress()"
                @change="selectBillingAddress()"
              >
                <el-option
                  v-for="item in addresses"
                  :key="item.full_name"
                  :label="item.full_name"
                  :value="item.id"
                />
              </el-select>
            </el-form-item>
            <el-form-item label="Door No / Flat No" prop="door_no">
              <el-input v-model="billing_address.door_no" disabled />
            </el-form-item>
            <el-form-item label="Full Name" prop="full_name">
              <el-input v-model="billing_address.full_name" disabled />
            </el-form-item>
            <el-form-item label="Address" prop="address">
              <el-input
                v-model="billing_address.address"
                type="textarea"
                disabled
                :rows="2"
                placeholder="Address"
              />
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
                <div class="cal-amount">
                  <span>{{ balance | convert_with_symbol }}</span>
                </div>
              </div>
              <div class="calculations">
                <div class="cal-grand">
                  <span>Amount Payable</span>
                </div>
                <div class="cal-amount">
                  <span>{{ temp.grand_total | convert_with_symbol }}</span>
                </div>
              </div>
              <div class="">
                <div class="payment-mode">
                  <el-form-item label="" prop="payment_mode">
                    <el-select
                      v-model="temp.payment_mode"
                      style="width: 100%"
                      autocomplete="off"
                      filterable
                      placeholder="Payment Mode"
                    >
                      <el-option
                        v-for="item in payment_modes"
                        :key="item.name"
                        :label="item.name"
                        :value="item.name"
                        :disabled="item.disabled ? item.disabled : false"
                      />
                    </el-select>
                  </el-form-item>
                </div>
              </div>
              <div class="make-payment-btn">
                <el-button
                  class="checkout"
                  type="success"
                  round
                  size="large"
                  icon="el-icon-shopping-cart-full"
                  :loading="buttonLoading"
                  @click="placeCombo()"
                  >Make Payment</el-button
                >
              </div>
            </div>
          </el-col>
        </el-form>
      </div>
    </el-row>
    <el-row v-if="step == 2" :gutter="10" style="margin-top: 20px;">
      <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
        <div class="order-success pb-16">
          <center>
            <h4
              class="middel-text text-2xl mt-10 leading-8 mb-1 text-gray-700 font-bold px-3 md:px-0"
            >
              Your order has been placed successfully
            </h4>
            <h1
              style="text-align: center;"
              class="text-2xl mt-2 leading-8 mb-1 text-gray-800 font-bold"
            >
              Order No #{{ order_no }}
            </h1>
            <img
              :src="orderSuccess"
              alt=""
              style="max-height: 350px;max-width: 350px;"
            />
          </center>
        </div>
      </el-col>
    </el-row>
    <el-dialog
      title="Add Address"
      width="60%"
      top="30px"
      :fullscreen="is_mobile"
      :visible.sync="dialogAddressesVisible"
    >
      <el-form
        ref="AddressData"
        :rules="rulesAddress"
        :model="tempAddress"
        style=""
      >
        <el-row :gutter="10">
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <el-form-item label="Full Name" prop="full_name">
              <el-input v-model="tempAddress.full_name" />
            </el-form-item>
            <!--   <el-form-item label="Mobile Number" prop="mobile_number">
              <el-input v-model="tempAddress.mobile_number" />
            </el-form-item> -->
            <el-form-item label="Mobile Number" prop="mobile_number">
              <el-input
                v-model="tempAddress.mobile_number"
                type="text"
                auto-complete="on"
                placeholder="Enter valid Mobile No."
              >
                <el-select
                  v-model="tempAddress.country_code"
                  class="countryFlag"
                  slot="prepend"
                  placeholder="Country"
                  filterable
                  prop="country_code"
                  style="width: 110px !important;"
                >
                  <el-option
                    v-for="item in Country"
                    :key="item.city_country"
                    :label="item.phonecode + '  ' + item.country_img"
                    :value="item.phonecode"
                  >
                    <span style="float: left">{{ item.phonecode }}</span>
                    <span
                      style="float: right; color: #8492a6; font-size: 13px"
                      >{{ item.country_img }}</span
                    >
                  </el-option>
                </el-select>
              </el-input>
            </el-form-item>

            <el-form-item label="Default ?" prop="is_default">
              <br />
              <el-checkbox
                v-model="tempAddress.is_default"
                label="Default Address ?"
                border
              ></el-checkbox>
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <el-form-item label="Door No / Flat No" prop="door_no">
              <el-input v-model="tempAddress.door_no" />
            </el-form-item>
            <el-form-item label="Address" prop="address">
              <el-input v-model="tempAddress.address" />
            </el-form-item>
            <el-form-item label="Landmark" prop="landmark">
              <el-input v-model="tempAddress.landmark" />
            </el-form-item>

            <el-form-item label="Country" prop="country">
              <el-select
                v-model="tempAddress.country"
                style="width: 100%"
                filterable
                @change="handleCountryChange"
                placeholder="Select Country"
              >
                <el-option
                  v-for="item in Country"
                  :key="item.country_img"
                  :label="item.city_country"
                  :value="item.city_country"
                >
                  <span style="float: left">{{ item.city_country }}</span>
                  <span style="float: right; color: #8492a6; font-size: 13px">{{
                    item.country_img
                  }}</span>
                </el-option>
              </el-select>
            </el-form-item>

            <el-form-item label="State" prop="state">
              <el-select
                v-model="tempAddress.state"
                style="width: 100%"
                filterable
                @change="handleStateChange"
                placeholder="Select Province/State"
              >
                <el-option
                  v-for="item in states"
                  :key="item"
                  :label="item"
                  :value="item"
                >
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="City" prop="city">
              <br />
              <el-select
                v-model="tempAddress.city"
                style="width: 100%"
                filterable
                placeholder="Select City"
              >
                <el-option
                  v-for="item in cities"
                  :key="item"
                  :label="item"
                  :value="item"
                >
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
        <el-button
          size="mini"
          type="primary"
          icon="el-icon-finished"
          :loading="buttonLoading"
          @click="dialogStatus === 'create' ? createData() : updateData()"
        >
          Save
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import {
  getSizesByCategory,
  getColorsByCategory,
} from '@/api/user/shopping';

import {
  getCombo,
  getProductsBySizeAndColor,
  placeCombo,
} from '@/api/user/combos';
import waves from '@/directive/waves';
import { getAllAddresses, createAddress } from '@/api/user/addresses';
import {
  getCurrencies,
  getAllCountry,
  getCountryStates,
  getStateCities,
  getPackages,
} from '@/api/user/config';
import { getSettings } from "@/api/user/settings";
import { parseTime } from '@/utils';
import Pagination from '@/components/Pagination';
import defaultSettings from '@/settings';
import { getMyBalance } from '@/api/user/wallet';
import orderSuccess from '@/assets/images/order-success.png';

const { pvLabel } = defaultSettings;

export default {
  name: 'ProductStock',
  components: {
    Pagination,
  },
  directives: {
    waves,
  },
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
      pvLabel,
      products: [],
      combo: [],
      productQuantity: [],
      currentProduct: {
        product_id: undefined,
        variant_id: undefined,
        color_id: undefined,
        size_id: undefined,
      },
      listQuery: {
        selectedCategorySizes: {},
        selectedCategorySizes: {},
      },
      colors: [],
      sizes: [],
      comboCategorySizes: {},
      comboCategoryColors: {},
      comboCategoryProducts: {},
      selectedCategoryProducts: {},
      selectedCategorySizes: {},
      selectedCategoryColors: {},
      product: {},
      buttonLoading: undefined,
      temp: {
        gstin: undefined,
        shipping_address_id: undefined,
        billing_address_id: undefined,
        productVariantIds: [],
        grand_total: 0,
        shipping:0,
      },
      orderRules: {
        shipping_address_id: [
          {
            required: true,
            message: 'Shipping Adddress is required',
            trigger: 'change',
          },
        ],
        payment_mode: [
          {
            required: true,
            message: 'Please select payment mode.',
            trigger: 'change',
          },
        ],
      },

      tempAddress: {
        id: undefined,
        full_name: undefined,
        door_no: undefined,
        mobile_number: undefined,
        pincode: undefined,
        country_code: undefined,
        address: undefined,
        landmark: undefined,
        city: undefined,
        state: undefined,
        country: undefined,
        is_default: true,
      },
      rulesAddress: {
        full_name: [
          {
            required: true,
            message: 'Name is required',
            trigger: 'blur',
          },
        ],
        door_no: [
          {
            required: true,
            message: 'Door No / Flat No is required',
            trigger: 'blur',
          },
        ],
        mobile_number: [
          {
            required: true,
            validator: validateContact,
            trigger: 'blur',
          },
        ],
        address: [
          {
            required: true,
            message: 'Address is required',
            trigger: 'blur',
          },
        ],
        city: [
          {
            required: true,
            message: 'City is required',
            trigger: 'blur',
          },
        ],
        state: [
          {
            required: true,
            message: 'State is required',
            trigger: 'blur',
          },
        ],
        country: [
          {
            required: true,
            message: 'Country is required',
            trigger: 'blur',
          },
        ],
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
      dialogAddressesVisible: false,
      step: 0,
      dialogStatus: undefined,
      dialogTitle: undefined,
      addresses: [],
      order_no: undefined,
      balance: 0.0,
      is_create: true,
      downloadLoading: false,
      buttonLoading: false,
      Country: [],
      states: [],
      cities: [],
      settings: { shipping_charge: 0,shipping_charge_2:0 },
      billing_address_tick: false,
      processStatus: 'process',
      payment_modes: [
        {
          id: 1,
          name: 'Wallet',
        },
        // {
        //   id: 2,
        //   name: 'Online',
        //   disabled: true,
        // }
      ],
      is_shipping_waiver:true,
      orderSuccess: orderSuccess,
    };
  },
  created() {
     this.getSettings();
    this.getCombo(this.$route.params.id);
    this.getAllAddresses();
    if (window.screen.width <= '550') {
      this.is_mobile = true;
    }
    getAllCountry().then(response => {
      this.Country = response.data;
    });
    this.getMyBalance();
  },
  methods: {
    getCombo(id) {
      this.listLoading = true;
      getCombo(id).then(response => {
        const combo = response.data;
        this.combo = combo;
        this.temp.grand_total =parseFloat(this.combo.net_amount);
        this.getComboFilters(combo);
        if(!this.combo.is_shipping_waiver){
          this.is_shipping_waiver=false;
        }
        if(!this.is_shipping_waiver){
          if(this.temp.grand_total < this.settings.shipping_criteria){
            this.temp.shipping=parseFloat(this.settings.shipping_charge_2);
          }else{
            this.temp.shipping=parseFloat(this.settings.shipping_charge);
          }
        }
        this.temp.grand_total+=this.temp.shipping;
      });
    },
    getMyBalance() {
      getMyBalance().then(response => {
        this.balance = response.data;
      });
    },
    getAllAddresses() {
      getAllAddresses().then(response => {
        this.addresses = response.data;
      });
    },
    getSettings() {
      getSettings().then(response => {
        this.settings = response.data  
        this.tempAddress.country_code = response.data.default_country_code;

      });
    },
    handleStateChange() {
      this.tempAddress.city = undefined;
      this.cities = [];
      getStateCities(this.tempAddress.state).then(response => {
        this.cities = response.data;
      });
    },
    handleCountryChange() {
      this.tempAddress.city = undefined;
      this.tempAddress.state = undefined;
      getCountryStates(this.tempAddress.country).then(response => {
        this.states = response.data;
      });
    },
    selectShippingAddress() {
      this.temp.billing_address_id = undefined;
      this.resetBillingAddress();
      this.billing_address_tick = false;
      const address = this.addresses.filter(address => {
        return address.id == this.temp.shipping_address_id;
      });
      if (address[0]) {
        this.shipping_address = address[0];
      }
    },
    resetTemp() {
      this.temp = {
        gstin: undefined,
        shipping: 0,
        discount: 0,
        grand_total: 0,
        pv: 0,
        distributor_discount: 0,
        shipping_address_id: undefined,
        billing_address_id: undefined,
        productVariantIds: [],
      };
    },
    resetTempAddress() {
      this.tempAddress = {
        id: undefined,
        full_name: undefined,
        door_no: undefined,
        mobile_number: undefined,
        country_code: undefined,
        pincode: undefined,
        address: undefined,
        landmark: undefined,
        city: undefined,
        state: undefined,
        country: undefined,
        is_default: true,
      };
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
    selectBillingAddress() {
      const address = this.addresses.filter(address => {
        return address.id == this.temp.billing_address_id;
      });
      if (address[0]) {
        this.billing_address = address[0];
      }
    },
    gotoStep1() {
      let check = true;
      let productVariantIds = [];

      this.combo.categories.forEach(comboCategory => {
        if (comboCategory.quantity > 1) {
          for (let qty = 1; qty <= comboCategory.quantity; qty++) {
            let key = comboCategory.category_id + '_' + qty;
            if (
              !(
                this.selectedCategoryProducts[key] &&
                this.selectedCategoryProducts[key]['id']
              )
            ) {
              this.$notify({
                title: 'Error',
                message: 'All poducts in the combo needs to be selected',
                type: 'error',
                duration: 2000,
              });
              check = false;
              return false;
            } else {
              productVariantIds.push({
                category_id: comboCategory.category_id,
                product_variant_id: this.selectedCategoryProducts[key]['id'],
              });
            }
          }
        } else {
          let key = comboCategory.category_id + '_' + 1;
          if (
            !(
              this.selectedCategoryProducts[key] &&
              this.selectedCategoryProducts[key]['id']
            )
          ) {
            this.$notify({
              title: 'Error',
              message: 'All poducts in the combo needs to be selected',
              type: 'error',
              duration: 2000,
            });
            check = false;
            return false;
          } else {
            productVariantIds.push({
              category_id: comboCategory.category_id,
              product_variant_id: this.selectedCategoryProducts[key]['id'],
            });
          }
        }
      });

      if (check) {
        this.temp.productVariantIds = productVariantIds;
        this.step = 1;
      }
    },
    getComboFilters(combo) {
      combo.categories.forEach((comboCategory, index) => {
        let key = '';

        if (comboCategory.quantity > 1) {
          getColorsByCategory(comboCategory.category_id).then(response => {
            for (let qty = 1; qty <= comboCategory.quantity; qty++) {
              key = comboCategory.category_id + '_' + qty;
              this.$set(this.comboCategoryColors, key, response.data);
            }
          });

          getSizesByCategory(comboCategory.category_id).then(response => {
            for (let qty = 1; qty <= comboCategory.quantity; qty++) {
              key = comboCategory.category_id + '_' + qty;
              this.$set(this.comboCategorySizes, key, response.data);
            }
          });
          this.getProductsBySizeAndColor(comboCategory.category_id,key);
        } else {
          key = comboCategory.category_id + '_' + 1;
          getColorsByCategory(comboCategory.category_id).then(response => {
            this.$set(this.comboCategoryColors, key, response.data);
          });

          getSizesByCategory(comboCategory.category_id).then(response => {
            this.$set(this.comboCategorySizes, key, response.data);
          });
          this.getProductsBySizeAndColor(comboCategory.category_id,key);
        }
      });
    },
    getProductsBySizeAndColor(categoryId, key) {

      delete this.product[key];
      delete this.selectedCategoryProducts[key];

      let color_id = this.selectedCategoryColors[key] || 0;
      let size_id = this.selectedCategorySizes[key] || 0;

      getProductsBySizeAndColor(categoryId, size_id, color_id).then(
        response => {
          this.$set(this.comboCategoryProducts, key, response.data);
        }
      );
    },
    getProduct(key){
      this.product[key]=this.selectedCategoryProducts[key].product;
    },
    handleCreate() {
      this.resetTempAddress();
      this.dialogStatus = 'create';
      this.dialogTitle = 'Add Address';
      this.getSettings();
      this.dialogAddressesVisible = true;
    },
    createData() {
      this.$refs['AddressData'].validate(valid => {
        if (valid) {
          this.buttonLoading = true;
          createAddress(this.tempAddress)
            .then(data => {
              this.getAllAddresses();
              this.dialogAddressesVisible = false;
              this.buttonLoading = false;
              this.$notify({
                title: 'Success',
                message: data.message,
                type: 'success',
                duration: 2000,
              });
              this.buttonLoading = false;
              this.resetTempAddress();
            })
            .catch(err => {
              this.buttonLoading = false;
            });
        }
      });
    },
    placeCombo() {
      this.$refs['orderForm'].validate(valid => {
        this.temp.combo_id = this.combo.id;
        if (valid) {
          if (parseFloat(this.balance) < parseFloat(this.temp.grand_total)) {
            this.$message.error(
              'Oops, You have not enough balance to place order.'
            );
            return;
          }
          this.buttonLoading = true;
          placeCombo(this.temp)
            .then(response => {
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
            })
            .catch(error => {
              this.buttonLoading = false;
            });
        }
      });
    },
  },
};
</script>

<style scoped>
.pagination-container {
  margin-top: 5px;
  background: #fff;
  padding: 15px 16px;
}

.app-container {
  height: calc(94vh - 50px);
}

#categoryScroll::-webkit-scrollbar-track,
#productsScroll::-webkit-scrollbar-track,
#cartScroll::-webkit-scrollbar-track {
  -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
  border-radius: 10px;
  background-color: #f5f5f5;
}

#categoryScroll::-webkit-scrollbar,
#productsScroll::-webkit-scrollbar,
#cartScroll::-webkit-scrollbar {
  width: 8px;
  background-color: #f5f5f5;
}

.item {
  padding: 20px 30px;
  /* height: 120px; */
  display: flex;
}

.item {
  /*border-top:  1px solid #E1E8EE;*/
  border-bottom: 1px solid #e1e8ee;
}

/* Product Image */
.image {
  margin-right: 30px;
  /* width: 100px; */
  text-align: center;
}

/* Product Description */
.description {
  padding-top: 10px;
  margin-right: 30px;
  width: 200px;
}

.description-base {
  padding-top: 10px;
  width: 200px;
}

.description-base span {
  display: block;
  font-size: 14px;
  color: #43484d;
  font-weight: 400;
}

.description span {
  display: block;
  font-size: 10px;
  color: #43484d;
  font-weight: 200;
}

/* Product Quantity */
.quantity {
  padding-top: 20px;
  margin-right: 10px;
}

.quantity input {
  -webkit-appearance: none;
  border: none;
  text-align: center;
  width: 32px;
  font-size: 16px;
  color: #43484d;
  font-weight: 300;
}

.calculations {
  padding: 5px 7px;
  height: 30px;
  display: flex;
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

.checkout-btn {
  padding: 15px 15px 15px 15px;
}

.make-payment-btn {
  padding: 15px 15px 15px 15px;
  margin: 0 auto;
}

.checkout-btn {
  padding: 15px 15px 15px 15px;
}

.checkout-btn button {
  float: right;
}

button[class*='btn'] {
  width: 30px;
  height: 30px;
  background-color: #e1e8ee;
  border-radius: 6px;
  border: none;
  cursor: pointer;
}

.checkout-btn button {
  float: right;
}

.cal-amount {
  width: 70%;
  margin-right: 25px;
  padding-top: 0px;
  text-align: right;
  font-size: 16px;
  color: #43484d;
  font-weight: 600;
}

.shopping-cart {
  background: #ffffff;
  /*  box-shadow: 0px 1px 10px 5px rgba(0,0,0,0.10);
  border-radius: 6px;*/
  display: flex;
  flex-direction: column;
}

#categoryScroll::-webkit-scrollbar-thumb,
#productsScroll::-webkit-scrollbar-thumb,
#cartScroll::-webkit-scrollbar-thumb {
  border-radius: 10px;
  -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
  background-color: #adadad;
}
</style>
