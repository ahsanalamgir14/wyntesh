<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="listQuery.search" size="mini" placeholder="Search Records" class="filter-item mobile_class" @keyup.enter.native="handleFilter" style="width: 200px;" />
      <el-button v-waves class="filter-item " size="mini" type="primary" icon="el-icon-search" @click="handleFilter">Search</el-button>
      <el-button class="filter-item" style="margin-left: 10px;" size="mini" type="success" @click="handleCreate"><i class="fas fa-plus"></i> Add</el-button>
    </div>
    <el-table :key="tableKey" v-loading="listLoading" :data="list" border fit highlight-current-row style="width: 100%;">
      <el-table-column label="Sr#" prop="id" align="center" type="index" :index="indexMethod" width="70">
      </el-table-column>
      <el-table-column label="Actions" align="center" width="150px" class-name="small-padding">
        <template slot-scope="{row}">
          <el-tooltip content="Edit" placement="right" effect="dark">
            <el-button type="primary" :loading="buttonLoading" icon="el-icon-edit" circle @click="handleEdit(row)"></el-button>
          </el-tooltip>
          <el-tooltip content="Delete" placement="right" effect="dark">
            <el-button icon="el-icon-delete" circle type="danger" @click="deleteData(row)"></el-button>
          </el-tooltip>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Full Name" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.full_name }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Mobile Number" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.mobile_number }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Address" min-width="270px">
        <template slot-scope="{row}">
          <span>{{ row.address }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Landmark" min-width="270px">
        <template slot-scope="{row}">
          <span>{{ row.landmark }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="City" min-width="270px">
        <template slot-scope="{row}">
          <span>{{ row.city }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="State" min-width="270px">
        <template slot-scope="{row}">
          <span>{{ row.state }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Pincode" min-width="270px">
        <template slot-scope="{row}">
          <span>{{ row.pincode }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Created at" width="150px">
        <template slot-scope="{row}">
          <span>{{ row.created_at | parseTime('{d}-{m}-{y}') }}</span>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />
    <el-dialog :title="textMap[dialogStatus]" width="60%" top="30px" :fullscreen="is_mobile" :visible.sync="dialogAddressesVisible">
      <el-form ref="dataForm" :rules="rules" :model="temp" style="">
        <el-row :gutter="10">
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <el-form-item label="Full Name" prop="full_name">
              <el-input v-model="temp.full_name" />
            </el-form-item>
<!--             
            <el-form-item label="Mobile Number" prop="mobile_number">
              <el-input v-model="temp.mobile_number" />
            </el-form-item> -->

            <el-form-item label="Mobile Number" prop="mobile_number">
                <el-input  v-model="temp.mobile_number" type="text" auto-complete="on" placeholder="Enter valid Mobile No." >
                    <el-select v-model="temp.country_code" class="countryFlag" slot="prepend" placeholder="Country" filterable  prop="country_code" style="width: 110px !important;">
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
              <el-checkbox v-model="temp.is_default" size="mini" label="Default Address ?" border></el-checkbox>
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <el-form-item label="Door No / Flat No" prop="door_no">
              <el-input v-model="temp.door_no" />
            </el-form-item>
            <el-form-item label="Address" prop="address">
              <el-input v-model="temp.address" />
            </el-form-item>
            <el-form-item label="Landmark" prop="landmark">
              <el-input v-model="temp.landmark" />
            </el-form-item>
           <!--  <el-form-item label="City" prop="city">
              <el-input v-model="temp.city" />
            </el-form-item>
            <el-form-item label="State" prop="state">
              <el-input v-model="temp.state" />
            </el-form-item> -->

            <el-form-item label="Country" prop="country">
                <el-select v-model="temp.country" style="width: 100%" filterable @change="handleCountryChange" placeholder="Select Country">
                    <el-option
                      v-for="item in Country"
                      :key="item.city_country"
                      :label="item.city_country"
                      :value="item.city_country">
                      <span style="float: left">{{ item.city_country }}</span>
                      <span style="float: right; color: #8492a6; font-size: 13px">{{ item.country_img }}</span>
                    </el-option>
                </el-select>
            </el-form-item>

          <el-form-item label="State"  prop="state">
            <el-select v-model="temp.state" style="width: 100%" filterable @change="handleStateChange" placeholder="Select Province/State">
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
                <el-select v-model="temp.city"  style="width: 100%" filterable placeholder="Select City">
                    <el-option
                        v-for="item in cities"
                        :key="item"
                        :label="item"
                        :value="item">
                    </el-option>
                </el-select>
            </el-form-item>


            <el-form-item label="Pincode" prop="pincode">
              <el-input v-model="temp.pincode" />
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
  </div>
</template>
<script>
import {
  fetchAddresses,
  deleteAddress,
  createAddress,
  updateAddress
} from "@/api/user/addresses";
import { getCurrencies,getAllCountry,getCountryStates ,getStateCities, getPackages } from '@/api/user/config';
import waves from "@/directive/waves";
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";
import { getPublicSettings } from '@/api/user/settings';

export default {
  name: "addresses",
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
      tableKey: 0,
      list: null,
      total: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 5,
        search: undefined,
        sort: "+id"
      },
      temp: {
        id: undefined,
        full_name: undefined,
        door_no: undefined,
        mobile_number: undefined,
        pincode: undefined,
        address: undefined,
        landmark: undefined,
        city: undefined,
        state: undefined,
        country_code: undefined,
        country: undefined,
        is_default: true
      },
      rules: {
        full_name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        door_no: [{ required: true, message: 'Door No / Flat No is required', trigger: 'blur' }],
        mobile_number: [{ required: true, validator: validateContact, trigger: 'blur' }],
        pincode: [{ required: true, message: 'Pincode is required', trigger: 'blur' }],
        address: [{ required: true, message: 'Address is required', trigger: 'blur' }],
        city: [{ required: true, message: 'City is required', trigger: 'blur' }],
        state: [{ required: true, message: 'State is required', trigger: 'blur' }],
        country: [{ required: true, message: 'Country is required', trigger: 'blur' }],
      },
      dialogAddressesVisible: false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      settings: { default_country_code: 0 },
      buttonLoading: false,
      states: [],
      cities: [],
      Country:[],
    };
  },
  created() {
    this.getList();
    if (window.screen.width <= '550') {
      this.is_mobile = true;
    }

    getAllCountry().then(response => {
        this.Country = response.data;
    });

  },
  methods: {
     handleStateChange(){
        this.temp.city = undefined;
        getStateCities(this.temp.state).then(response => {
            this.cities = response.data;
        });
    },
    handleCountryChange(){
        this.temp.city = undefined;
        this.temp.state = undefined;
        getCountryStates(this.temp.country).then(response => {
            this.states = response.data;
        });
    },
    getPublicSettings() {
        getPublicSettings().then(response => {
            this.settings = response.data;
            let tempSettings = Object.assign({}, response.data);
            if(!this.temp.country_code){
              this.temp.country_code      = response.data.default_country_code;
            }
        });
    },
    indexMethod(index) {
      let page = this.listQuery.page;
      if (this.listQuery.page == 1) {
        let tempIndex = index * 1;
        let total = this.total + 1;
        return total - (tempIndex + 1);
      } else {

        let tempIndex = this.listQuery.limit * (page - 1) + index;;
        let total = this.total + 1;
        return total - (tempIndex + 1);
      }
    },
    getList() {
      this.listLoading = true;
      fetchAddresses(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
    resetTemp() {
      this.temp = {
        id: undefined,
        full_name: undefined,
        door_no: undefined,
        mobile_number: undefined,
        pincode: undefined,
        address: undefined,
        country_code: undefined,
        landmark: undefined,
        city: undefined,
        state: undefined,
        is_default: true
      };
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogTitle = "Add Address";
      this.getPublicSettings();
      this.dialogAddressesVisible = true;
      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });

    },
    createData() {
      this.$refs["dataForm"].validate(valid => {
        if (valid) {

          this.buttonLoading = true;
          createAddress(this.temp).then((data) => {
            this.getList();
            this.dialogAddressesVisible = false;
            this.buttonLoading = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading = false;
            this.resetTemp();
          }).catch((err) => {
            this.buttonLoading = false;
          });
        }
      });
    },
    handleEdit(row) {
      this.temp = Object.assign({}, row); // copy obj
      this.temp.is_default = this.temp.is_default ? true : false;
      this.dialogStatus = "update";
      this.dialogTitle = "Update Address";
      this.dialogAddressesVisible = true;
      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });
    },
    updateData() {

      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          let tempData = Object.assign({}, this.temp);
          tempData.is_default = tempData.is_default ? 1 : 0;

          this.buttonLoading = true;
          updateAddress(tempData).then((data) => {
            this.getList();
            this.dialogAddressesVisible = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading = false;
            this.resetTemp();
          }).catch((err) => {
            this.buttonLoading = false;
          });
        }
      });
    },
    deleteData(row) {
      this.$confirm('Are you sure you want to delete ?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        deleteAddress(row.id).then((data) => {
          this.dialogAddressesVisible = false;
          this.$notify({
            title: "Success",
            message: data.message,
            type: "success",
            duration: 2000
          });
          const index = this.list.indexOf(row);
          this.list.splice(index, 1);
        });
      });
    }
  }
};

</script>
<style scoped>

.pagination-container {
  background: #fff;
  padding: 15px 16px;
  margin-top: 5px;
}

</style>
