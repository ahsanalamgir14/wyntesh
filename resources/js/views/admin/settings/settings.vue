t<template>
  <div class="app-container">
    <el-form ref="dataForm" :rules="rules" :model="temp" label-position="top"  style="">
      <el-tabs type="border-card">
        <el-tab-pane label="Company Settings">
          <el-row :gutter="20">
            <el-col  :xs="24" :sm="24" :md="12" :lg="8" :xl="8" >
              <el-form-item label="TDS Percentage" prop="tds_percentage">
                <el-input type="number" v-model="temp.tds_percentage" />
              </el-form-item>
              <el-form-item label="Minimun Parchase for Activation (PV)" prop="minimum_purchase">
                <el-input type="number" v-model="temp.minimum_purchase" />
              </el-form-item>              
              <el-form-item label="Automatic Payout ?" prop="is_automatic_payout">
                <el-select v-model="temp.is_automatic_payout"  style="width:100%;" filterable placeholder="Select Payout Mode">
                    <el-option label="Yes" value="1"></el-option>
                    <el-option label="No" value="0"></el-option>
                </el-select>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row >
            <hr>
            <div style="float: right;margin-top:10px;">
              <el-button
                type="success"
                icon="el-icon-finished" :loading="buttonLoading"
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
  getCompanySettings,
  saveCompanySettings,
} from "@/api/admin/company-settings";

export default {
  name: "Settings",
  data() {
    return {
      temp: { 
        tds_percentage:undefined,
        minimum_purchase:undefined,
        is_automatic_payout:"0",
      },
      rules: {
        tds_percentage: [
          { required: true, message: "TDS Percentage is required.", trigger: "blur" }
        ],
        minimum_purchase: [
          { required: true, message: "Minimun purchase is required.", trigger: "blur" }
        ],
        is_automatic_payout: [
          { required: true, message: "Select Payout Mode.", trigger: "blur" }
        ],
      },
      buttonLoading: false
    };
  },
  created() {
    getCompanySettings().then(response => {
      this.temp = response.data;
    });
  },
  methods: {
    handleSaveSettings() {
      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          this.buttonLoading=true;
          saveCompanySettings(this.temp).then((response) => {
            this.temp=response.data;
            this.buttonLoading=false;
            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            });
          }).catch((err)=>{
            this.buttonLoading=false;
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
