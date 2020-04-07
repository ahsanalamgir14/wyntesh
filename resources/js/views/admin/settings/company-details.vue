<template>
  <div class="app-container">
    <el-form ref="dataForm" :rules="rules" :model="temp" label-position="top"  style="">
      <el-tabs type="border-card">
        <el-tab-pane label="Company Details">          
          <el-row :gutter="20">
            <el-col  :xs="24" :sm="24" :md="12" :lg="8" :xl="8" >
              <el-form-item label="Company Name" prop="company_name">
                <el-input v-model="temp.company_name" />
              </el-form-item>
              <el-form-item label="About" prop="company_about">
                <el-input v-model="temp.company_about" />
              </el-form-item>
              <el-form-item label="Website" prop="website">
                <el-input v-model="temp.website" />
              </el-form-item>
              <el-form-item label="Tag Line" prop="tag_line">
                <el-input v-model="temp.tag_line" />
              </el-form-item>
              
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="8" :xl="8" >
              <el-form-item label="Contact Email" prop="contact_email">
                <el-input v-model="temp.contact_email" />
              </el-form-item>
              <el-form-item label="Support Email" prop="support_email">
                <el-input v-model="temp.support_email" />
              </el-form-item>                
              <el-form-item label="Contact Phone" prop="contact_email">
                <el-input v-model="temp.contact_phone" />
              </el-form-item>
              <el-form-item label="Support Phone" prop="support_phone">
                <el-input v-model="temp.support_phone" />
              </el-form-item>
              
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="8" :xl="8" >
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
        <el-tab-pane label="Social Media">
          <el-row :gutter="20">
            <el-col  :xs="24" :sm="24" :md="12" :lg="8" :xl="8" >
              <el-form-item label="Facebook" prop="facebook_link">
                <el-input v-model="temp.facebook_link" />
              </el-form-item>
              <el-form-item label="Youtube" prop="youtube_link">
                <el-input v-model="temp.youtube_link" />
              </el-form-item>                
              <el-form-item label="Instagram" prop="instagram_link">
                <el-input v-model="temp.instagram_link" />
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
      </el-tabs>
    </el-form>
  </div>
</template>

<script>
import {
  getAdminSettings,
  saveCompanySettings,
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
        company_name:undefined,
        company_about:undefined,
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
        
        facebook_link:undefined,
        youtube_link:undefined,
        instagram_link:undefined,
        gplus_link:undefined,
        twitter_link:undefined,        

      },
      rules: {
        company_name: [
          { required: true, message: "Company Name is required.", trigger: "blur" }
        ],
        company_about: [
          { required: true, message: "Company About is required.", trigger: "blur" }
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
        facebook_link: [
          { required: true, message: "Facebook link is required.", trigger: "blur" }
        ],
        youtube_link: [
          { required: true, message: "Youtube link is required.", trigger: "blur" }
        ],
        twitter_link: [
          { required: true, message: "Twitter is required.", trigger: "blur" }
        ],
        instagram_link: [
          { required: true, message: "Instagram link is required.", trigger: "blur" }
        ],


      },
      buttonLoading: false
    };
  },
  created() {
    getAdminSettings().then(response => {
      this.temp = response.data;
    });
  },
  methods: {
    handleSaveSettings() {
      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          saveCompanySettings(this.temp).then((response) => {
            this.temp=response.data;
            this.$notify({
              title: "Success",
              message: response.message,
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
