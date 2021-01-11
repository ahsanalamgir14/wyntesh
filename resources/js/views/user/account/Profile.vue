<template>
  <div class="app-container">
    <el-row :gutter="20">
      <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
        <el-alert
          v-if="temp.kyc.remarks && temp.kyc.verification_status=='rejected'"
          style="margin-bottom: 10px;"
          title="KYC Rejection"
          type="error"
          :description="temp.kyc.remarks"
          show-icon
        />
      </el-col>
    </el-row>
    <el-form ref="profileForm"  :model="temp" :rules="profileRules">
      <el-row :gutter="20">
        <el-col :xs="24" :sm="24" :md="12" :lg="6" :xl="6">
          <el-card>
            <div class="user-profile">
              <div class="user-avatar box-center">
                <pan-thumb :image="temp.profile_picture?temp.profile_picture:'/images/avatar.png'" :height="'100px'" :width="'100px'" :hoverable="false" />
              </div>
              <div class="box-center">
                <div class="user-name text-center">
                  {{ temp.name }}
                  </br></br>

                  <el-tag v-if="temp.kyc.verification_status=='verified'" type="success">Verified</el-tag>
                  <el-tag v-if="temp.kyc.verification_status=='pending'" type="warning">KYC - Pending</el-tag>
                  <el-tag v-if="temp.kyc.verification_status=='submitted'" type="primary">KYC - Submitted</el-tag>
                  <el-tag v-if="temp.kyc.verification_status=='rejected'" type="danger">KYC - Rejected</el-tag>
                </div>
                <div class="user-role text-center text-muted">
                  <h4 style="margin-bottom:7px ">Joined on</h4>
                  {{ temp.created_at | parseTime('{d}-{m}-{y}') }}
                </div>
                <div style="margin-top:10px;">
                  <el-button type="warning" round v-clipboard:copy="referral_link" v-clipboard:success="onCopy" >Copy referral link</el-button>
                </div>
              </div>
            </div>
          </el-card>
        </el-col>
        <el-col :xs="24" :sm="24" :md="12" :lg="18" :xl="18">
          <el-tabs v-model="activeTab" type="border-card">
            <el-tab-pane v-loading="updating" label="Basic Details" name="details">
              <el-row :gutter="20">
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                  <el-form-item label="Name">
                    <el-input v-model="temp.name" disabled/>
                  </el-form-item>
                  <el-form-item label="Email">
                    <el-input v-model="temp.email" disabled />
                  </el-form-item>
                  <el-form-item label="Username" prop="username">
                    <el-input v-model="temp.username" disabled />
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                   <el-form-item label="Currency" prop="currency">
                    <br>
                    <el-select  v-model="temp.currency_code"  filterable placeholder="Select Currency">
                      <el-option
                        v-for="item in currencyList"
                        :key="item.code"
                        :label="item.name"
                        :value="item.code">
                      </el-option>
                    </el-select>
                  </el-form-item>
                    <el-form-item prop="contact" label="Mobile No">
                
                    <el-input v-model="temp.contact" type="text" auto-complete="on" placeholder="Enter valid Mobile No." >
                            <el-select v-model="temp.country_code" class="countryFlag" slot="prepend" placeholder="Country" filterable  prop="country_code" style="    width: 110px !important;">
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



                  <el-form-item label="Gender" prop="gender">
                    </br>
                    <el-radio-group v-model="temp.gender" size="mini">
                      <el-radio label="m" border>Male</el-radio>
                      <el-radio label="f" border> Female</el-radio>
                      <el-radio label="o" border>Transgender</el-radio>
                    </el-radio-group>
                  </el-form-item>

                  <el-form-item label="DOB" prop="dob">
                    </br>
                    <el-date-picker
                      v-model="temp.dob"
                      type="date"
                      format="dd-MM-yyyy"
                      value-format="yyyy-MM-dd"
                      placeholder="Date of birth"
                    />
                  </el-form-item>
                  <el-form-item label="GSTIN" prop="gstin">
                    <el-input v-model="temp.gstin" />
                  </el-form-item>                 

                </el-col>
              </el-row>



              <el-form-item>
                <el-button type="primary" size="mini" icon="el-icon-finished" :loading="buttonLoading"  @click="onSubmit">
                  Update
                </el-button>
              <el-button type="primary"  size="mini" :loading="buttonLoading"  @click="goToNextPage" style="float: right;">
                  Next
                </el-button>
               <!--  <el-button type="primary"  :loading="buttonLoading"  @click="goToBackPage" style="float: right;">
                  Back
                </el-button> -->

              </el-form-item>
            </el-tab-pane>
            <el-tab-pane v-loading="updating" label="Kyc and Bank" name="kyc">
              <el-row :gutter="20">
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">

                  <el-form-item prop="adhar">
                    <label class="label">Aadhaar </label>
                    <el-input v-model="temp.kyc.adhar" max="12" :disabled="temp.kyc.verification_status=='submitted' || temp.kyc.verification_status=='verified' "/>
                  </el-form-item>
                  
                <!--   <el-form-item prop="state">
                    <label class="label"> State </label>
                    <el-select v-model="temp.kyc.state" style="width: 100%" filterable @change="handleStateChange" placeholder="Select State">
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
                    <el-select v-model="temp.kyc.city"  style="width: 100%" filterable placeholder="Select City">
                      <el-option
                        v-for="item in cities"
                        :key="item"
                        :label="item"
                        :value="item">
                      </el-option>
                    </el-select>
                  </el-form-item> -->

                    <el-form-item prop="country">
                        <label class="label"> Country </label>
                        <el-select v-model="temp.kyc.country" style="width: 100%" filterable @change="handleCountryChange" placeholder="Select Country">
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

                  <el-form-item prop="state">
                    <label class="label"> State </label>
                    <el-select v-model="temp.kyc.state" style="width: 100%" filterable @change="handleStateChange" placeholder="Select Province/State">
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
                        <el-select v-model="temp.kyc.city"  style="width: 100%" filterable placeholder="Select City">
                            <el-option
                                v-for="item in cities"
                                :key="item"
                                :label="item"
                                :value="item">
                            </el-option>
                        </el-select>
                    </el-form-item>
               

                  
                  <el-form-item prop="address">
                    <label class="label"> Address </label><br>
                    <el-input
                      v-model="temp.kyc.address"
                      type="textarea"
                      :rows="2"
                      placeholder="Address" 
                    />
                  </el-form-item>
                  <el-form-item label="" prop="pincode">
                    <label class="label"> Pincode </label>
                    <el-input v-model="temp.kyc.pincode"  />
                  </el-form-item>

                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                  <el-form-item  prop="pan">
                    <label class="label"> Pancard </label>
                    <el-input v-model="temp.kyc.pan" max="10" :disabled="temp.kyc.verification_status=='submitted' || temp.kyc.verification_status=='verified'" />
                  </el-form-item>
                  <el-form-item  prop="bank_ac_name">
                    <label class="label"> Bank A/C Name </label>
                    <el-input v-model="temp.kyc.bank_ac_name" />
                  </el-form-item>
                  <el-form-item  prop="bank_name">
                    <label class="label"> Bank Name </label>
                    <el-input v-model="temp.kyc.bank_name" />
                  </el-form-item>
                  <el-form-item  prop="bank_ac_no">
                    <label class="label"> Bank A/C No </label>
                    <el-input v-model="temp.kyc.bank_ac_no"  />
                  </el-form-item>
                  <el-form-item  prop="ifsc">
                    <label class="label"> IFSC Code </label>
                    <el-input v-model="temp.kyc.ifsc"  />
                  </el-form-item>
                </el-col>
              </el-row>


              <el-form-item>
                <el-button type="primary" size="mini" icon="el-icon-finished" :loading="buttonLoading"  @click="onSubmit">
                  Update
                </el-button>
                    <el-button type="primary" size="mini" :loading="buttonLoading"  @click="goToNextPage" style="float: right;">
                  Next
                </el-button>

                <el-button type="primary" size="mini" :loading="buttonLoading"  @click="goToBackPage" style="float: right;">
                  Back
                </el-button>
              </el-form-item>
            </el-tab-pane>
            <el-tab-pane v-loading="updating" label="Nominee Details" name="nominee">
              <el-row :gutter="20">
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">

                  <el-form-item label="Nominee Name" prop="nominee_name">
                    <el-input v-model="temp.kyc.nominee_name" max="64" />
                  </el-form-item>
                  <el-form-item label="Nominee Relation" prop="nominee_relation">
                    <br>
                    <el-select v-model="temp.kyc.nominee_relation" style="width: 100%" clearable placeholder="Select Nominee">
                      <el-option label="Father" value="Father" />
                      <el-option label="Mother" value="Mother" />
                      <el-option label="Brother" value="Brother" />
                      <el-option label="Sister" value="Sister" />
                      <el-option label="Wife" value="Wife" />
                      <el-option label="Son" value="Son" />
                      <el-option label="Daughter" value="Daughter" />
                    </el-select>
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                  <el-form-item label="Nominee DOB" prop="nominee_dob" style="width:72%">
                    <br>
                    <el-date-picker
                      v-model="temp.kyc.nominee_dob"
                      type="date"
                      format="dd-MM-yyyy"
                      value-format="yyyy-MM-dd"
                      placeholder="Date of birth"
                    />
                  </el-form-item>
                  <el-form-item label="Nominee Mobile">
                   <el-input v-model="temp.kyc.nominee_contact" type="text" auto-complete="on" placeholder="Enter valid Mobile No." >
                            <el-select v-model="temp.kyc.country_code" class="countryFlag" slot="prepend" placeholder="Country" filterable  prop="nominee_country_code" style="width: 110px !important;">
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


                </el-col>
              </el-row>


              <el-form-item>
                <el-button type="primary" size="mini" icon="el-icon-finished" :loading="buttonLoading"  @click="onSubmit">
                  Update
                </el-button>
                 <el-button type="primary" size="mini" :loading="buttonLoading"  @click="goToNextPage" style="float: right;">
                  Next
                </el-button>
                <el-button type="primary"  size="mini" :loading="buttonLoading"  @click="goToBackPage" style="float: right;">
                  Back
                </el-button>
              </el-form-item>
            </el-tab-pane>
            <el-tab-pane v-loading="updating" label="Profile Image and KYC Images" name="kyc-images">
              <el-row :gutter="20">
                <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                  <div class="img-upload">
                    <el-form-item prop="profile_picture">
                      <label for="Profile Picture">Profile Picture</label>
                      <el-upload
                        ref="upload"
                        class="avatar-uploader"
                        action="#"
                        :show-file-list="true"
                        :auto-upload="false"
                        :on-change="handleProfileChange"
                        :on-remove="handleProfileRemove"
                        :limit="1"
                        :file-list="profilefileList"
                        :on-exceed="handleExceed"
                        
                        accept="image/png, image/jpeg"
                      >

                        <img v-if="temp.profile_picture" :src="temp.profile_picture?temp.profile_picture:''" class="avatar">
                        <i v-if="temp.profile_picture" slot="default" class="el-icon-plus" />
                        <i v-else class="el-icon-plus avatar-uploader-icon" />
                      </el-upload>
                      <a v-if="temp.profile_picture" :href="temp.profile_picture?temp.profile_picture:''" target="_blank">View full image.</a>
                    </el-form-item>
                  </div>
                </el-col>
                <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                  <div class="img-upload">
                    <el-form-item prop="adhar_image">
                      <label for="Adhar Front Image"> Aadhaar Front Image</label>
                      <el-upload
                        ref="upload"
                        class="avatar-uploader"
                        action="#"
                        :show-file-list="true"
                        :auto-upload="false"
                        :on-change="handleAdharChange"
                        :on-remove="handleAdharRemove"
                        :limit="1"
                        :file-list="adharfileList"
                        :on-exceed="handleExceed"
                        :disabled="temp.kyc.verification_status=='submitted' || temp.kyc.verification_status=='verified' "
                        accept="image/png, image/jpeg"
                      >

                        <img v-if="temp.kyc.adhar_image" :src="temp.kyc?temp.kyc.adhar_image:''" class="avatar">
                        <i v-if="temp.kyc.adhar_image" slot="default" class="el-icon-plus" />
                        <i v-else class="el-icon-plus avatar-uploader-icon" />
                      </el-upload>
                      <a v-if="temp.kyc.adhar_image" :href="temp.kyc?temp.kyc.adhar_image:''" target="_blank">View full image.</a>
                    </el-form-item>
                  </div>
                </el-col>
                <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                  <div class="img-upload">
                    <el-form-item prop="adhar_image_back">
                      <label for="Adhar Back Image"> Aadhaar Back Image</label>
                      <el-upload
                        ref="upload"
                        class="avatar-uploader"
                        action="#"
                        :show-file-list="true"
                        :auto-upload="false"
                        :on-change="handleAdharBackChange"
                        :on-remove="handleAdharBackRemove"
                        :limit="1"
                        :disabled="temp.kyc.verification_status=='submitted' || temp.kyc.verification_status=='verified' "
                        :file-list="adharBackfileList"
                        :on-exceed="handleExceed"
                        accept="image/png, image/jpeg"
                      >

                        <img v-if="temp.kyc.adhar_image_back" :src="temp.kyc?temp.kyc.adhar_image_back:''" class="avatar">
                        <i v-if="temp.kyc.adhar_image_back" slot="default" class="el-icon-plus" />
                        <i v-else class="el-icon-plus avatar-uploader-icon" />
                      </el-upload>
                      <a v-if="temp.kyc.adhar_image_back" :href="temp.kyc?temp.kyc.adhar_image_back:''" target="_blank">View full image.</a>
                    </el-form-item>
                  </div>
                </el-col>
              </el-row>
              <el-row :gutter="20">

                <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                  <div class="img-upload">
                    <el-form-item prop="pan_image">
                      <label for="Pan Image"> Pancard Image</label>
                      <el-upload
                        ref="upload"
                        class="avatar-uploader"
                        action="#"
                        :show-file-list="true"
                        :auto-upload="false"
                        :on-change="handlePanChange"
                        :on-remove="handlePanRemove"
                        :limit="1"
                        :file-list="panfileList"
                        :on-exceed="handleExceed"
                        :disabled="temp.kyc.verification_status=='submitted' || temp.kyc.verification_status=='verified' "
                        accept="image/png, image/jpeg"
                      >

                        <img v-if="temp.kyc.pan_image" :src="temp.kyc?temp.kyc.pan_image:''" class="avatar">
                        <i v-if="temp.kyc.pan_image" slot="default" class="el-icon-plus" />
                        <i v-else class="el-icon-plus avatar-uploader-icon" />
                      </el-upload>
                      <a v-if="temp.kyc.pan_image" :href="temp.kyc?temp.kyc.pan_image:''" target="_blank">View full image.</a>
                    </el-form-item>
                  </div>
                </el-col>

                <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                  <div class="img-upload">
                    <el-form-item prop="cheque_image">
                      <label for="Bank/Cheque Image Image">Bank/Cheque Image</label>
                      <el-upload
                        ref="upload"
                        class="avatar-uploader"
                        action="#"
                        :show-file-list="true"
                        :auto-upload="false"
                        :on-change="handleChequeChange"
                        :on-remove="handleChequeRemove"
                        :limit="1"
                        :file-list="chequefileList"
                        
                        :on-exceed="handleExceed"
                        accept="image/png, image/jpeg"
                      >

                        <img v-if="temp.kyc.cheque_image" :src="temp.kyc?temp.kyc.cheque_image:''" class="avatar">
                        <i v-if="temp.kyc.cheque_image" slot="default" class="el-icon-plus" />
                        <i v-else class="el-icon-plus avatar-uploader-icon" />
                      </el-upload>
                      <a v-if="temp.kyc.cheque_image" :href="temp.kyc?temp.kyc.cheque_image:''" target="_blank">View full image.</a>
                    </el-form-item>
                  </div>
                </el-col>

                 <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >
                  <div class="img-upload">
                    <el-form-item  prop="distributor_image">
                      <label for="Distributor Contract"> Direct Seller Aggreement </label>
                      <el-upload
                        class="avatar-uploader"
                        action="#"
                         ref="upload"
                        :show-file-list="true"
                        :auto-upload="false"
                        :on-change="handleDistributerChange"
                        :on-remove="handleDistributerRemove"
                        :disabled="temp.kyc.verification_status=='submitted' || temp.kyc.verification_status=='verified' "
                        :limit="1"
                        :file-list="distributerfileList"
                        :on-exceed="handleExceed"
                        accept="image/png, image/jpeg , .docx , .pdf">
                        
                        <img v-if="temp.kyc.distributor_image" :src="temp.kyc?temp.kyc.distributor_image?temp.kyc.distributor_image:'':documentimg" class="avatar">
                        <i v-if="temp.kyc.distributor_image"  slot="default" class="el-icon-plus"></i>
                        <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                      </el-upload> 
                      <a  v-if="temp.kyc.distributor_image" :href="temp.kyc?temp.kyc.distributor_image:''" target="_blank">View</a>                     
                    </el-form-item>
                  </div>
                </el-col>

              </el-row>

              <el-form-item style="margin-top:20px;">
                <el-button type="primary" size="mini" icon="el-icon-finished" :loading="buttonLoading"  @click="onSubmit">
                  Update
                </el-button>
                    <el-button type="primary" size="mini" :loading="buttonLoading"  @click="goToBackPage" style="float: right;">
                  Back
                </el-button>
       
                <el-button style="margin:0;" type="success" size="mini" icon="el-icon-finished" :loading="buttonLoading" :disabled="temp.kyc.verification_status=='submitted' || temp.kyc.verification_status=='verified' " @click="submitVerification">
                  {{ temp.kyc.verification_status=='submitted' ?'Submitted for apporval': 'Submit for verification' }}
                </el-button>

              </el-form-item>
            </el-tab-pane>
          </el-tabs>
        </el-col>
      </el-row>
    </el-form>
  </div>
</template>

<script>
import { updateProfile, getProfile } from '@/api/user/members';
import { getCurrencies,getAllCountry,getCountryStates ,getStateCities, getPackages } from '@/api/user/config';


import PanThumb from '@/components/PanThumb';
import { parseTime } from '@/utils';
import { getPublicSettings } from '@/api/user/settings';
import documentimg from '@/assets/images/document.png'
import defaultSettings from '@/settings';

const { baseUrl } = defaultSettings;


export default {
  name: 'Profile',
  components: { PanThumb },
  data() {
    const validateContact = (rule, value, callback) => {
      var pattern = /^[1-9][0-9]{6,11}$/;
      if (!pattern.test(value)) {
        callback(new Error('Enter valid mobile number. (min 7 digit, max 12 digit)'));
      } else {
        callback();
      }
    };

    const validateNomineeContact  = (rule, value, callback) => {
      value=this.temp.kyc.nominee_contact;
      if(!value){
        callback();
      }

      var pattern = /^[1-9][0-9]{6,11}$/;
      if (!pattern.test(value)) {
        callback(new Error('Enter valid mobile number. (min 7 digit, max 12 digit)'));
      } else {
        callback();
      }
    };
    const validateAdhar  = (rule, value, callback) => {
      value=this.temp.kyc.adhar;
      
      if(!value){
        callback();
      }

      var pattern = /^[0-9]{12}$/;
      if (!pattern.test(value)) {
        callback(new Error('Enter 12 digit Adhar No.'));
      } else {
        callback();
      }
    };
    const validateBirthdate  = (rule, value, callback) => {

      value=this.temp.dob;
      
      if(!value){
        callback();
      }else{
        var optimizedBirthday = value.replace(/-/g, "/");
        //set date based on birthday at 01:00:00 hours GMT+0100 (CET)
        var myBirthday = new Date(optimizedBirthday);
        // set current day on 01:00:00 hours GMT+0100 (CET)
        var currentDate = new Date().toJSON().slice(0,10)+' 01:00:00';
        // calculate age comparing current date and borthday
        var myAge = ~~((Date.now(currentDate) - myBirthday) / (31557600000));

        if(myAge < 18) {
          callback(new Error('You must be atleast 18 year old.'));
        }else{
          callback();
        }
      }
    };
    return {      
      temp: {
        id: undefined,
        name: undefined,
        username: undefined,
        email: undefined,
        password: undefined,
        contact: undefined,
        country_code: undefined,
        currency_code:undefined,
        gender: 'm',
        kyc: {
          address: undefined,
          pincode: undefined,
          adhar: undefined,
          adhar_image: undefined,
          pan: undefined,
          pan_image: undefined,
          pan_cheque: undefined,
          city: undefined,
          state: undefined,
          country: undefined,
          bank_ac_name: undefined,
          bank_name: undefined,
          bank_ac_no: undefined,
          ifsc: undefined,
          nominee_name: undefined,
          nominee_relation: undefined,
          nominee_dob: undefined,
          nominee_contact: undefined,
          country_code: undefined,
        },
        old_kyc_status:undefined,
        profile_picture: undefined,
        parent: undefined,
        comission_from_self: 0,
        comission_from_child: 0,
        dob: undefined,
        is_active: 0,
      },
      profileRules: {
        contact: [{ required: true, trigger: 'blur', validator: validateContact  }],
        adhar: [{ required: true, trigger: 'blur', validator: validateAdhar  }],
        nominee_contact: [{ required: false, trigger: 'blur', validator: validateNomineeContact  }],
        dob: [{ required: false, trigger: 'blur', validator: validateBirthdate  }],
      },
      Country:[],
      activeTab: 'details',
      documentimg: documentimg,
      updating: false,
      buttonLoading: false,
      referral_link: '',
      adharfileList: [],
      adharfile: undefined,
      adharBackfileList: [],
      adharBackfile: undefined,
      profilefileList: [],
      profilefile: undefined,
      panfileList: [],
      panfile: undefined,
      chequefileList: [],
      chequefile: undefined,
      distributerfileList:[],
      distributerFile:undefined,
      currencyList:[],
      states:[],
      cities:[],
      settings: { default_country_code: 0 },
    };
  },
  created() {
    
    getProfile().then(response => {
      this.temp = response.data;
      if (!this.temp.kyc){
        this.temp.kyc = {
          address: undefined,
          pincode: undefined,
          adhar: undefined,
          pan: undefined,
          city: undefined,
          state: undefined,
          country: undefined,
          bank_ac_name: undefined,
          bank_name: undefined,
          bank_ac_no: undefined,
          ifsc: undefined,
          nominee_name: undefined,
          nominee_relation: undefined,
          nominee_dob: undefined,
          nominee_contact: undefined,
          country_code: undefined,
        };
      }
      this.referral_link=baseUrl+'#/register?sponsor_code='+this.temp.username;
    });

    getCurrencies().then(response => {
      this.currencyList = response.data;
    });


    getAllCountry().then(response => {
        this.Country = response.data;
    });
    this.getPublicSettings();

  },
  methods: {
    getPublicSettings() {
        getPublicSettings().then(response => {
            this.settings = response.data;
            this.temp.kyc.country_code  = response.data.default_country_code;
            if(!this.temp.country_code){
              this.temp.country_code      = response.data.default_country_code;
            }
        });
    },
    handleStateChange(){
        this.temp.kyc.city = undefined;
        getStateCities(this.temp.kyc.state).then(response => {
            this.cities = response.data;
        });
    },
    handleCountryChange(){
        this.temp.kyc.city = undefined;
        this.temp.kyc.state = undefined;
        getCountryStates(this.temp.kyc.country).then(response => {
            this.states = response.data;
        });
    },
    onCopy(){
      this.$notify({
          title: "Copied",
          message: "Referral link copied successfully.",
          type: "success",
          duration: 2000
        })
 
    },
    goToNextPage() {
        if(this.activeTab == "details"){
            this.activeTab = "kyc";
        }else if(this.activeTab == "kyc"){
            this.activeTab = "nominee";
        }else if(this.activeTab == "nominee"){
            this.activeTab = "kyc-images";
        }
    },
    goToBackPage() {
        // alert(this.activeTab);
        if(this.activeTab == "nominee"){
            this.activeTab = "kyc";
        }
        else if(this.activeTab == "kyc-images"){
            this.activeTab = "nominee";
        }
        else if(this.activeTab == "kyc"){
            this.activeTab = "details";
        }
    },
    handleAdharChange(f, fl){
      if (fl.length > 1){
        fl.shift();
      }
      this.adharfile = f.raw;
    },
    // handleStateChange(){
    //   getStateCities(this.temp.kyc.state).then(response => {
    //     this.cities = response.data;
    //   });
    // },
    handleAdharRemove(file, fileList) {
      this.adharfile = undefined;
      this.adharfileList = [];
    },
    handleAdharBackChange(f, fl){
      if (fl.length > 1){
        fl.shift();
      }
      this.adharBackfile = f.raw;
    },
    handleAdharBackRemove(file, fileList) {
      this.adharBackfile = undefined;
      this.adharBackfileList = [];
    },
    handleProfileRemove(file, fileList) {
      this.profilefile = undefined;
      this.profilefileList = [];
    },
    handleProfileChange(f, fl){
      if (fl.length > 1){
        fl.shift();
      }
      this.profilefile = f.raw;
    },
    handlePanChange(f, fl){
      if (fl.length > 1){
        fl.shift();
      }
      this.panfile = f.raw;
    },
    handlePanRemove(file, fileList) {
      this.panfile = undefined;
      this.panfileList = [];
    },
    handlePanChange(f, fl){
      if (fl.length > 1){
        fl.shift();
      }
      this.panfile = f.raw;
    },
    handleChequeChange(f, fl){
      if (fl.length > 1){
        fl.shift();
      }
      this.chequefile = f.raw;
    },
    handleDistributerChange(f, fl){     
      if(fl.length > 1){
        fl.shift()  
      }      
      this.distributerFile=f.raw      
    },
    handleChequeRemove(file, fileList) {
      this.chequefile = undefined;
      this.chequefileList = [];
    },
    handleDistributerRemove(file, fileList) {
       this.distributerFile=undefined;
       this.distributerfileList=[];
    },
    handleExceed(files, fileList) {
      this.$message.warning(`You can not select more than one file, please remove first.`);
    },
    submitVerification(){
      let old_temp=Object.assign({}, this.temp); 
      this.old_kyc_status=old_temp.kyc.verification_status;
      this.$refs.profileForm.validate(valid => {
        if (valid) {
          this.temp.kyc.verification_status = 'submitted';
          this.onSubmit();    
        }
      });
      
    },
    clean(obj) {
        for (var propName in obj) {
            if (obj[propName] === null || obj[propName] === undefined) {
                delete obj[propName];
            }
        }
    },
    onSubmit() {

      this.$refs.profileForm.validate(valid => {
        if (valid) {
          this.updating = true;
          this.buttonLoading = true;

          var form = new FormData();
          this.clean(this.temp);
          const form_data = this.temp;

          for (var key in form_data) {
            if (form_data[key] !== undefined && form_data[key] !== null){
              if (key == 'kyc'){
                form.append(key, JSON.stringify(form_data[key]));
              } else {
                form.append(key, form_data[key]);
              }
            }
          }

          form.append('adhar_image', this.adharfile);
          form.append('adhar_image_back', this.adharBackfile);
          form.append('profile_picture', this.profilefile);
          form.append('pan_image', this.panfile);
          form.append('cheque_image', this.chequefile);
          form.append('distributor_image', this.distributerFile);

          updateProfile(form).then((response) => {
            this.updating = false;
            this.buttonLoading = false;
            this.temp = response.data;
            this.$notify({
              title: 'Success',
              message: 'Profile updated Successfully',
              type: 'success',
              duration: 2000,
            });
            this.adharfile = undefined;
            this.adharfileList = [];
            this.adharBackfile = undefined;
            this.adharBackfileList = [];
            this.profilefile = undefined;
            this.profilefileList = [];
            this.panfile = undefined;
            this.panfileList = [];
            this.chequefile = undefined;
            this.chequefileList = [];
            this.distributerFile=undefined
            this.distributerfileList=[];
          }).catch((err) => {
            this.updating = false;
            this.buttonLoading = false;
            this.temp.kyc.verification_status = this.old_kyc_status;
          });
        }
      });

      
    },
  },
};
</script>

<style lang="scss" scoped>
.el-radio.is-bordered + .el-radio.is-bordered {
    margin-left: 2px !important;
}

.user-activity {
  .user-block {
    .username, .description {
      display: block;
      margin-left: 50px;
      padding: 2px 0;
    }
    img {
      width: 40px;
      height: 40px;
      float: left;
    }
    :after {
      clear: both;
    }
    .img-circle {
      border-radius: 50%;
      border: 2px solid #d2d6de;
      padding: 2px;
    }
    span {
      font-weight: 500;
      font-size: 12px;
    }
  }
  .post {
    font-size: 14px;
    border-bottom: 1px solid #d2d6de;
    margin-bottom: 15px;
    padding-bottom: 15px;
    color: #666;
    .image {
      width: 100%;
    }
    .user-images {
      padding-top: 20px;
    }
  }
  .list-inline {
    padding-left: 0;
    margin-left: -5px;
    list-style: none;
    li {
      display: inline-block;
      padding-right: 5px;
      padding-left: 5px;
      font-size: 13px;
    }
    
  }
  .el-carousel__item h3 {
    color: #475669;
    font-size: 14px;
    opacity: 0.75;
    line-height: 200px;
    margin: 0;
  }

  .el-carousel__item:nth-child(2n) {
    background-color: #99a9bf;
  }

  .el-carousel__item:nth-child(2n+1) {
    background-color: #d3dce6;
  }
}

@media (min-width:750px) {
  .img-upload{
    float: right;
    margin-right:20px;
  }
}

.avatar {
    width: 200px;
    height: 115px;
    display: block;
  }

  .avatar-uploader-icon {
    font-size: 28px;
    color: #8c939d;
    width: 200px;
    height: 115px !important;
    line-height: 115px;
    text-align: center;
  }

.user-profile {
  .user-name {
    font-weight: bold;
  }
  .box-center {
    padding-top: 10px;
  }
  .user-role {
    padding-top: 10px;
    font-weight: 400;
    font-size: 14px;
  }
  .box-social {
    padding-top: 30px;
    .el-table {
      border-top: 1px solid #dfe6ec;
    }
  }
  .user-follow {
    padding-top: 20px;
  }
}

label{
  color:#606266;
}

label-mandatory{
  color:red;
}

.el-radio{
  margin-right: 0px;
}
</style>
