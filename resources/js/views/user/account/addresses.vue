<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="listQuery.search"
        placeholder="Search Records"
        style="width: 200px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-button
        v-waves
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >Search</el-button>
      <el-button
        class="filter-item"
        style="margin-left: 10px;"
        type="success"
        @click="handleCreate"
      ><i class="fas fa-plus"></i> Add</el-button>
    </div>

    <el-table
      :key="tableKey"
      v-loading="listLoading"
      :data="list"
      border
      fit
      highlight-current-row
      style="width: 100%;"
      @sort-change="sortChange"
    >
      <el-table-column
        label="ID"
        prop="id"
        sortable="custom"
        align="center"
        width="80"
        :class-name="getSortClass('id')"
      >
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Actions" align="center" width="150px" class-name="small-padding">
        <template slot-scope="{row}">
          <el-button
            type="primary"
            :loading="buttonLoading"
            icon="el-icon-edit"
            circle
            @click="handleEdit(row)"
          ></el-button>
          <el-button
              icon="el-icon-delete"
              circle
              type="danger"
              @click="deleteData(row)"
          ></el-button>
        </template>
      </el-table-column>
      <el-table-column label="Full Name" min-width="150px">
        <template slot-scope="{row}">
          <span  >{{ row.full_name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Mobile Number" min-width="150px">
        <template slot-scope="{row}">
          <span  >{{ row.mobile_number }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Address" min-width="270px">
        <template slot-scope="{row}">
          <span  >{{ row.address }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Landmark" min-width="270px">
        <template slot-scope="{row}">
          <span  >{{ row.landmark }}</span>
        </template>
      </el-table-column>
      <el-table-column label="City" min-width="270px">
        <template slot-scope="{row}">
          <span  >{{ row.city }}</span>
        </template>
      </el-table-column>
      <el-table-column label="State" min-width="270px">
        <template slot-scope="{row}">
          <span  >{{ row.state }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Pincode" min-width="270px">
        <template slot-scope="{row}">
          <span  >{{ row.pincode }}</span>
        </template>
      </el-table-column>
       <el-table-column label="Created at" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.created_at | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>
    </el-table>

    <pagination
      v-show="total>0"
      :total="total"
      :page.sync="listQuery.page"
      :limit.sync="listQuery.limit"
      @pagination="getList"
    />

    <el-dialog :title="textMap[dialogStatus]" width="60%" top="30px"  :visible.sync="dialogAddressesVisible">
      <el-form ref="dataForm" :rules="rules" :model="temp" style="">
        <el-row :gutter="10">
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item label="Full Name" prop="full_name">
              <el-input v-model="temp.full_name" />
            </el-form-item>
            <el-form-item label="Mobile Number" prop="mobile_number">
              <el-input v-model="temp.mobile_number" />
            </el-form-item>
            <el-form-item label="Default ?" prop="is_default">
              <br>
              <el-checkbox v-model="temp.is_default" label="Default Address ?" border></el-checkbox>
            </el-form-item>
          </el-col>
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <el-form-item label="Address" prop="address">
              <el-input v-model="temp.address" />
            </el-form-item>
            <el-form-item label="Landmark" prop="landmark">
              <el-input v-model="temp.landmark" />
            </el-form-item>
            <el-form-item label="City" prop="city">
              <el-input v-model="temp.city" />
            </el-form-item>
            <el-form-item label="State" prop="state">
              <el-input v-model="temp.state" />
            </el-form-item>
            <el-form-item label="Pincode" prop="pincode">
              <el-input v-model="temp.pincode" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogAddressesVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="dialogStatus==='create'?createData():updateData()">
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
import waves from "@/directive/waves"; 
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 
import Tinymce from '@/components/Tinymce'

export default {
  name: "addresses",
  components: { Pagination,Tinymce },
  directives: { waves },
  
  data() {
    return {
      tableKey: 0,
      list: null,
      total: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 5,
        search:undefined,
        sort: "+id"
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        id:undefined,
        full_name: undefined,
        mobile_number:undefined,
        pincode:undefined,
        address:undefined,
        landmark:undefined,
        city:undefined,
        state:undefined,
        is_default:true
      },

      dialogAddressesVisible:false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
         full_name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
         mobile_number: [{ required: true, message: 'Mobile Number is required', trigger: 'blur' }],
         pincode: [{ required: true, message: 'Pincode is required', trigger: 'blur' }],
         address: [{ required: true, message: 'Address is required', trigger: 'blur' }],
         city: [{ required: true, message: 'City is required', trigger: 'blur' }],
         state: [{ required: true, message: 'State is required', trigger: 'blur' }],
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
  },
  methods: {
   
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
        id:undefined,
        full_name: undefined,
        mobile_number:undefined,
        pincode:undefined,
        address:undefined,
        landmark:undefined,
        city:undefined,
        state:undefined,
        is_default:true
      };
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogTitle = "Add Address";
      this.dialogAddressesVisible = true;
      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });
    },
    createData() {
      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          
          this.buttonLoading=true;
          createAddress(this.temp).then((data) => {
            this.getList();
            this.dialogAddressesVisible = false;
            this.buttonLoading=false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading=false;
            this.resetTemp();
          }).catch((err)=>{
            this.buttonLoading=false;
          });
        }
      });
    },
    handleEdit(row) {
     
      this.temp = Object.assign({}, row); // copy obj
      this.temp.is_default=this.temp.is_default?true:false;
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
          this.buttonLoading=true;
          let tempData = Object.assign({}, this.temp);
          tempData.is_default=tempData.is_default?1:0;

          updateAddress(tempData).then((data) => {
            this.getList();
            this.dialogAddressesVisible = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading=false;
            this.resetTemp();
          }).catch((err)=>{
            this.buttonLoading=false;
          });
        }
      });
    },
    deleteData(row) {
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
    },    
    sortChange(data) {
      const { prop, order } = data;
      if (prop === "id") {
        this.sortByID(order);
      }
    },
    sortByID(order) {
      if (order === "ascending") {
        this.listQuery.sort = "+id";
      } else {
        this.listQuery.sort = "-id";
      }
      this.handleFilter();
    },
    getSortClass: function(key) {
      const sort = this.listQuery.sort;
      return sort === `+${key}`
        ? "ascending"
        : sort === `-${key}`
        ? "descending"
        : "";
    }
  }
};
</script>

<style scoped>
.el-drawer__body {
  padding: 20px;
}
.pagination-container {
  margin-top: 5px;
}
.pagination-container {
  background: #fff;
  padding: 15px 16px;
}
@media (min-width:750px) {
  .img-upload{
    float: right;
    margin-right:20px; 
  }
}
</style>
