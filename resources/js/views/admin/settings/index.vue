<template>
  <div class="app-container">
    <el-form ref="dataForm" :rules="rules" :model="temp" label-position="top"  style="">
      <el-tabs type="border-card">
        <el-tab-pane label="Details">          
          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="Name" prop="name">
                <el-input v-model="temp.name" />
              </el-form-item>
              <el-form-item label="Display Name" prop="display_name">
                <el-input v-model="temp.display_name" />
              </el-form-item>
              <el-form-item label="Alias" prop="alias">
                <el-input v-model="temp.alias" />
              </el-form-item>
              <el-form-item label="Tag Line" prop="tag_line">
                <el-input v-model="temp.tag_line" />
              </el-form-item>
              <el-form-item label="Website" prop="website">
                <el-input v-model="temp.website" />
              </el-form-item>
              
            </el-col>
            <el-col :span="8">
              <el-form-item label="Contact Email" prop="contact_email">
                <el-input v-model="temp.contact_email" />
              </el-form-item>
              <el-form-item label="Support Email" prop="support_email">
                <el-input v-model="temp.support_email" />
              </el-form-item>                
              <el-form-item label="Contact Phone" prop="contact_email">
                <el-input v-model="temp.contact_email" />
              </el-form-item>
              <el-form-item label="Support Phone" prop="support_phone">
                <el-input v-model="temp.support_phone" />
              </el-form-item>
              
            </el-col>
            <el-col :span="8">
              <el-form-item label="Address" prop="address">
                <el-input v-model="temp.address" />
              </el-form-item>                
              <el-form-item label="City" prop="city">
                <el-input v-model="temp.city" />
              </el-form-item>
              <el-form-item label="State" prop="state">
                <el-input v-model="temp.state" />
              </el-form-item>
              <el-form-item label="Country" prop="country">
                <el-input v-model="temp.country" />
              </el-form-item>
              <el-form-item label="PIN Code" prop="pincode">
                <el-input v-model="temp.pincode" />
              </el-form-item>
            </el-col>
          </el-row>
          <el-row >
            <hr>
            <div style="float: right;margin-top:10px;">
              <el-button
                type="success"
                @click="handleSaveSettings"
              >Save</el-button>
            </div>
          </el-row>
        </el-tab-pane>
        <el-tab-pane label="Company & Social Media">
          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="Company Name" prop="company_name">
                <el-input v-model="temp.company_name" />
              </el-form-item>
              <el-form-item label="About" prop="company_about">
                <el-input v-model="temp.company_about" />
              </el-form-item>
              <el-form-item label="Site Link" prop="company_site_link">
                <el-input v-model="temp.company_site_link" />
              </el-form-item>
              <el-form-item label="Tag Line" prop="tag_line">
                <el-input v-model="temp.tag_line" />
              </el-form-item>
              
            </el-col>
            <el-col :span="8">
              <el-form-item label="Facebook" prop="facebook_link">
                <el-input v-model="temp.facebook_link" />
              </el-form-item>
              <el-form-item label="Youtube" prop="youtube_link">
                <el-input v-model="temp.youtube_link" />
              </el-form-item>                
              <el-form-item label="Instagram" prop="instagram_link">
                <el-input v-model="temp.instagram_link" />
              </el-form-item>
              <el-form-item label="Google Plus" prop="gplus_link">
                <el-input v-model="temp.gplus_link" />
              </el-form-item> 
              <el-form-item label="Twitter" prop="twitter_link">
                <el-input v-model="temp.twitter_link" />
              </el-form-item>
              
            </el-col>
          </el-row>
          <el-row >
            <hr>
            <div style="float: right;margin-top:10px;">
              <el-button
                type="success"
                @click="handleSaveSettings"
              >Save</el-button>
            </div>
          </el-row>
        </el-tab-pane>
        <el-tab-pane label="Configurations">
          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="Allow Registration" prop="registration">
                <el-select v-model="temp.registration" style="width:100%" placeholder="Allow Registration">
                  <el-option value=1 label="Yes"></el-option>
                  <el-option value=0 label="No"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="Enable Coupon" prop="coupon">
                <el-select v-model="temp.coupon" style="width:100%" placeholder="Enable Coupon">
                  <el-option value=1 label="Yes"></el-option>
                  <el-option value=0 label="No"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="Enable Quizzes" prop="quiz">
                <el-select v-model="temp.quiz" style="width:100%" placeholder="Enable Quizzes">
                  <el-option value=1 label="Yes"></el-option>
                  <el-option value=0 label="No"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="API Login First ?" prop="api_login_first">
                <el-select v-model="temp.api_login_first" style="width:100%" placeholder="API Login First ?">
                  <el-option value=1 label="Yes"></el-option>
                  <el-option value=0 label="No"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="All courses visible ?" prop="all_courses_visible">
                <el-select v-model="temp.all_courses_visible" style="width:100%" placeholder="All courses visible ?">
                  <el-option value=1 label="Yes"></el-option>
                  <el-option value=0 label="No"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="Manual coupon activation by admin ?" prop="is_admin_activate_coupon">
                <el-select v-model="temp.is_admin_activate_coupon" style="width:100%" placeholder="Manual coupon activation by admin ?">
                  <el-option value=1 label="Yes"></el-option>
                  <el-option value=0 label="No"></el-option>
                </el-select>
              </el-form-item>
              
            </el-col>
            <el-col :span="8">
              <el-form-item label="Google Client ID" prop="google_client_id">
                <el-input v-model="temp.google_client_id" />
              </el-form-item>
              <el-form-item label="Google Client Secret" prop="google_client_secret">
                <el-input v-model="temp.google_client_secret" />
              </el-form-item>                
              <el-form-item label="Mailgun Domain" prop="mailgun_domain">
                <el-input v-model="temp.mailgun_domain" />
              </el-form-item>
              <el-form-item label="Mailgun Secret" prop="mailgun_secret">
                <el-input v-model="temp.mailgun_secret" />
              </el-form-item>
              <el-form-item label="Mail From Email" prop="mail_from">
                <el-input v-model="temp.mail_from" />
              </el-form-item>                            
            </el-col>
            <el-col :span="8">
              <el-form-item label="Mail From Name" prop="mail_from_name">
                <el-input v-model="temp.mail_from_name" />
              </el-form-item>
              <el-form-item label="Quiz Questions" prop="quiz_questions">
                <el-input type="number" min="10" v-model="temp.quiz_questions" />
              </el-form-item> 
              <el-form-item label="Quiz Time" prop="quiz_time">
                <el-input type="number" min="10" v-model="temp.quiz_time" />
              </el-form-item>
              <el-form-item label="Coupon Prefix" prop="coupon_prefix">
                <el-input v-model="temp.coupon_prefix" />
              </el-form-item>
              <el-form-item label="Coupon Default View ?" prop="coupon_default_view">
                <el-select v-model="temp.coupon_default_view" style="width:100%" placeholder="Coupon Default View">
                  <el-option value=1 label="Yes"></el-option>
                  <el-option value=0 label="No"></el-option>
                </el-select>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row >
            <hr>
            <div style="float: right;margin-top:10px;">
              <el-button
                type="success"
                @click="handleSaveSettings"
              >Save</el-button>
            </div>
          </el-row>
        </el-tab-pane>
      </el-tabs>
    </el-form>
  </div>
</template>

<script>
import {
  getSettings,
  saveSettings,
} from "@/api/settings";

import waves from "@/directive/waves"; 
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";
import axios from "axios";

export default {
  name: "Settings",
  components: { Pagination },
  directives: { waves },
  data() {
    return {
      temp: {
        name:undefined,
        display_name:undefined,
        alias:undefined,
        tag_line:undefined,
        website:undefined,
        contact_email:undefined,
        contact_phone:undefined,
        support_email:undefined,
        support_phone:undefined,
        address:undefined,
        city:undefined,
        state:undefined,
        country:undefined,
        pincode:undefined,
        
        company_name:undefined,
        company_about:undefined,
        company_site_link:undefined,
        facebook_link:undefined,
        youtube_link:undefined,
        instagram_link:undefined,
        gplus_link:undefined,
        twitter_link:undefined,        

        registration:undefined,
        coupon:undefined,
        quiz:undefined,
        api_login_first:undefined,
        all_courses_visible:undefined,
        is_admin_activate_coupon:undefined,
        quiz_questions:undefined,
        quiz_time:undefined,
        coupon_prefix:undefined,      
        coupon_default_view:undefined,
        google_client_id:undefined,
        google_client_secret:undefined,                
        mailgun_domain:undefined,
        mailgun_secret:undefined,
        mail_from:undefined,
        mail_from_name:undefined,          
      },
      rules: {
        name: [
          { required: true, message: "Name is required", trigger: "blur" }
        ],
        display_name: [
          { required: true, message: "Display Name is required", trigger: "blur" }
        ],
        alias: [
          { required: true, message: "Alias is required", trigger: "blur" }
        ],
        tag_line: [
          { required: true, message: "Tagline is required", trigger: "blur" }
        ],
        website: [
          { required: true, message: "Website is required", trigger: "blur" }
        ],
        contact_email: [
          { required: true, message: "Contact email is required", trigger: "blur" }
        ],
        contact_phone: [
          { required: true, message: "Contact Phone required", trigger: "blur" }
        ],
        support_email: [
          { required: true, message: "Support email is required.", trigger: "blur" }
        ],
        support_phone: [
          { required: true, message: "Support phone is required.", trigger: "blur" }
        ],
        address: [
          { required: true, message: "Address is required.", trigger: "blur" }
        ],
        city: [
          { required: true, message: "City is required.", trigger: "blur" }
        ],
        state: [
          { required: true, message: "State is required.", trigger: "blur" }
        ],
        country: [
          { required: true, message: "Country is required.", trigger: "blur" }
        ],
        pincode: [
          { required: true, message: "Pincode is required.", trigger: "blur" }
        ],
        company_name: [
          { required: true, message: "Company Name is required.", trigger: "blur" }
        ],
        company_about: [
          { required: true, message: "Company About is required.", trigger: "blur" }
        ],
        company_site_link: [
          { required: true, message: "Company site link is required.", trigger: "blur" }
        ],
        facebook_link: [
          { required: true, message: "Facebook link is required.", trigger: "blur" }
        ],
        youtube_link: [
          { required: true, message: "Youtube link is required.", trigger: "blur" }
        ],
        gplus_link: [
          { required: true, message: "G+ Link is required.", trigger: "blur" }
        ],
        twitter_link: [
          { required: true, message: "Twitter is required.", trigger: "blur" }
        ],
        instagram_link: [
          { required: true, message: "Instagram link is required.", trigger: "blur" }
        ],
        registration: [
          { required: true, message: "Select registration allowed?", trigger: "blur" }
        ],
        coupon: [
          { required: true, message: "Is coupon enabled?", trigger: "blur" }
        ],
        quiz: [
          { required: true, message: "Is quizzes enabled?", trigger: "blur" }
        ],
        api_login_first: [
          { required: true, message: "Is API Login First?", trigger: "blur" }
        ],
        all_courses_visible: [
          { required: true, message: "Is all courses visible?", trigger: "blur" }
        ],
        quiz_questions: [
          { required: true, message: "Number of quiz question to appear is required.", trigger: "blur" }
        ],
        quiz_time: [
          { required: true, message: "Quiz duration is requried.", trigger: "blur" }
        ],
        coupon_prefix: [
          { required: true, message: "Coupon prefix is required.", trigger: "blur" }
        ],
        coupon_default_view: [
          { required: true, message: "Is coupon default view?", trigger: "blur" }
        ],
        mailgun_domain: [
          { required: true, message: "Mailgun domain is required.", trigger: "blur" }
        ],
        mailgun_secret: [
          { required: true, message: "mailgun secret is required.", trigger: "blur" }
        ],
        mail_from: [
          { required: true, message: "Mail from email is required.", trigger: "blur" }
        ],
        mail_from_name: [
          { required: true, message: "Mail from name is required.", trigger: "blur" }
        ],
        is_admin_activate_coupon: [
          { required: true, message: "Manual coupon activation?", trigger: "blur" }
        ],


      },
      buttonLoading: false
    };
  },
  created() {
    getSettings().then(response => {
      this.temp = response.data;
    });
  },
  methods: {
    handleSaveSettings() {
      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          saveSettings(this.temp).then((response) => {
            this.temp=response.data;
            this.$notify({
              title: "Success",
              message: "Updated Successfully",
              type: "success",
              duration: 2000
            });
          });
        }
      });
    },
  }
};
</script>

<style scoped>

.edit-input {
  padding-right: 100px;
}

.el-form-item--medium .el-form-item__content, .el-form-item--medium .el-form-item__label {
    line-height: 0px;
}
</style>
