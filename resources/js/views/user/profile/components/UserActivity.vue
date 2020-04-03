<template>
  <el-card>
    <el-tabs v-model="activeActivity" @tab-click="handleClick">
      <el-tab-pane v-loading="updating" label="Basic Details" name="details">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Name">
              <el-input v-model="temp.name"  />
            </el-form-item>
            <el-form-item label="Email">
              <el-input disabled v-model="temp.email"  />
            </el-form-item>
            <el-form-item label="Username" prop="username">
              <el-input disabled v-model="temp.username" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            
            <el-form-item label="Contact" prop="contact">
              <el-input v-model="temp.contact" />
            </el-form-item>

            <el-form-item label="Gender" prop="gender">
              <el-radio-group v-model="temp.gender">
                <el-radio label="m">Male</el-radio>
                <el-radio label="f">Female</el-radio>
              </el-radio-group>
            </el-form-item>

            <el-form-item label="DOB" prop="dob">
              <el-date-picker
                v-model="temp.dob"
                type="date"
                format="yyyy-MM-dd"
                value-format="yyyy-MM-dd"
                placeholder="Date of birth">
              </el-date-picker>
            </el-form-item>
                       
          </el-col>
        </el-row>
        <el-form-item>
          <el-button type="primary"  @click="onSubmit">
            Update
          </el-button>
        </el-form-item>
      </el-tab-pane>
      <el-tab-pane v-loading="updating" label="Kyc and Bank" name="kyc">
        <el-row :gutter="20">
          <el-col :span="12">            
            
            <el-form-item label="Adhar" prop="adhar">
              <el-input max="16" v-model="temp.kyc.adhar" />
            </el-form-item>
           
            <el-form-item label="City" prop="city">
              <el-input  v-model="temp.kyc.city" />
            </el-form-item>
            <el-form-item label="State" prop="state">
              <el-input v-model="temp.kyc.state" />
            </el-form-item>
            <el-form-item label="Address" prop="address">
              <el-input type="textarea"
                :rows="2"
                placeholder="Address" v-model="temp.kyc.address" />
            </el-form-item>
            <el-form-item label="Pincode" prop="pincode">
              <el-input v-model="temp.kyc.pincode" />
            </el-form-item>

          </el-col>
          <el-col :span="12">
             <el-form-item label="Pan" prop="pan">
              <el-input max="10" v-model="temp.kyc.pan" />
            </el-form-item>
            <el-form-item label="Bank A/C Name" prop="bank_ac_name">
              <el-input v-model="temp.kyc.bank_ac_name"  />
            </el-form-item>
            <el-form-item label="Bank Name" prop="bank_name">
              <el-input v-model="temp.kyc.bank_name"  />
            </el-form-item>
            <el-form-item label="A/C No" prop="bank_ac_no">
              <el-input v-model="temp.kyc.bank_ac_no"  />
            </el-form-item>
            <el-form-item label="IFSC Code" prop="ifsc">
              <el-input v-model="temp.kyc.ifsc"  />
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item>
          <el-button type="primary"  @click="onSubmit">
            Update
          </el-button>
        </el-form-item>
      </el-tab-pane>
    </el-tabs>

  </el-card>
</template>

<script>
import { updateFranchiseProfile,getFranchiseProfile } from "@/api/franchises";
export default {
  props: {
    user: {
      type: Object,
      default: () => {
        return {
          id: undefined,
          name: undefined,
          username: undefined,
          email: undefined,
          password:undefined,
          contact: undefined,
          gender: "m",
          kyc:{
            address:undefined,
            pincode:undefined,
            adhar:undefined,
            pan:undefined,
            city:undefined,
            state:undefined,
            pin:undefined,
            tag:undefined,
            bank_ac_name:undefined,
            bank_name:undefined,
            bank_ac_no:undefined,
            ifsc:undefined
          },
          parent:undefined,
          comission_from_self:0,
          comission_from_child:0,
          dob: undefined,
          is_active: 0,
        };
      },
    },
  },
  data() {
    return {
      activeActivity: 'details',
      updating: false,
      temp:{
          id: undefined,
          name: undefined,
          username: undefined,
          email: undefined,
          password:undefined,
          contact: undefined,
          gender: "m",
          kyc:{
            address:undefined,
            pincode:undefined,
            adhar:undefined,
            pan:undefined,
            city:undefined,
            state:undefined,
            bank_ac_name:undefined,
            bank_name:undefined,
            bank_ac_no:undefined,
            ifsc:undefined
          },
          parent:undefined,
          comission_from_self:0,
          comission_from_child:0,
          dob: undefined,
          is_active: 0,
        },
    };
  },
  created() {
     getFranchiseProfile().then(response => {
        this.temp = response.data
    });
  },
  methods: {
    handleClick(tab, event) {
      //console.log('Switching tab ', tab, event);
    },
    onSubmit() {
      this.updating = true;
      updateFranchiseProfile(this.temp).then((response) => {
        this.updating = false;
        this.temp=response.data;
        this.$notify({
          title: "Success",
          message: "Profile updated Successfully",
          type: "success",
          duration: 2000
        })
      })
    },
  },
};
</script>

<style lang="scss" scoped>
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
    .link-black {
      &:hover, &:focus {
        color: #999;
      }
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
</style>
