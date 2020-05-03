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

      <el-select v-model="listQuery.package_id" @change="handleFilter"  clearable class="filter-item" style="width:200px;" filterable placeholder="Select Package">
        <el-option
          v-for="item in packages"
          :key="item.name"
          :label="item.name"
          :value="item.id">
        </el-option>
      </el-select>

      <el-select v-model="listQuery.status" @change="handleFilter"  clearable class="filter-item" style="width:200px;" filterable placeholder="Select Status">
        <el-option label="Used" value="Used"></el-option>
        <el-option label="Not Used" value="Not Used"></el-option>
      </el-select>


      <el-button
        v-waves
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >Search</el-button>
           
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
      <el-table-column label="PIN" width="140px" >
        <template slot-scope="{row}">
          <span>{{ row.pin_number }}</span>
        </template>
      </el-table-column>
       <el-table-column label="Owner" width="130px" >
        <template slot-scope="{row}">
          <span>{{ row.owner?row.owner.user.username:'' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Package" width="120px" >
        <template slot-scope="{row}">
          <span>{{ row.package.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Base Amount" width="120px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.base_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Tax %" width="120px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.tax_percentage }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Tax Amount" width="120px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.tax_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Total Amount" width="150px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.total_amount }}</span>
        </template>
      </el-table-column>
     
      <el-table-column label="Userd by" width="130px" >
        <template slot-scope="{row}">
          <span>{{ row.user?row.user.user.username:'' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Used at" width="150px" align="center">
        <template slot-scope="{row}">
          <span v-if="row.used_at">{{ row.used_at | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>
       <el-table-column label="Allocation date" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.allocated_at | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Note" min-width="150px" >
        <template slot-scope="{row}">
          <span>{{ row.note }}</span>
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
  </div>
</template>

<script>
import { fetchAllPins, deletePinRequest } from "@/api/admin/pins";
import { getPackages,  } from "@/api/user/config";

import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";

export default {
  name: "pin-requests",
  components: { Pagination },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        Approved: "success",
        Pending: "info",
        Rejected: "danger"
      };

      return statusMap[status];
    }
  },
  data() {
    return {
      tableKey: 0,
      list: [],
      total: 0,
      listLoading: false,
      listQuery: {
        page: 1,
        limit: 5,
        search:undefined,
        package: 0,
        approved: undefined,
        sort: "+id"
      },
      paymentModes:[],
      packages:[],
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        id:undefined,
        package_id: undefined,
        quantity: undefined,
        base_amount:undefined,
        tax_percentage: undefined,
        tax_amount: undefined,
        net_amount:undefined,
        payment_mode:undefined,
        reference:undefined,
        bank_id:undefined,
        note:undefined,

        username:undefined,
      },

      dialogPinGenerateVisible:false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        package_id: [{ required: true, message: 'Package is required', trigger: 'blur' }],
        quantity: [{  required: true, message: 'Quantity is required', trigger: 'blur' }],
        base_amount: [{  required: true, message: 'Base amount is required', trigger: 'blur' }],
        tax_percentage: [{  required: true, message: 'Tax percentage is required', trigger: 'blur' }],
        tax_amount: [{  required: true, message: 'Tax amount is required', trigger: 'blur' }],
        net_amount: [{  required: true, message: 'Total amount is required', trigger: 'blur' }],
        payment_mode: [{  required: true, message: 'Payment mode is required', trigger: 'blur' }],
        reference: [{  required: true, message: 'Payment reference no is required', trigger: 'blur' }],
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
    this.getConfig();
  },
  methods: {    
    getList() {
      this.listLoading = true;
      fetchAllPins(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    getConfig() {      
      getPackages().then(response => {
        this.packages = response.data;
      });
    },    
    resetTemp() {
      this.temp = {
        id:undefined,
        package_id: undefined,
        quantity: undefined,
        amount:undefined,
        // tax_percentage: undefined,
        // tax_amount: undefined,
        // net_amount:undefined,
        payment_mode:undefined,
        reference:undefined,
        bank_id:undefined,
        note:undefined,
      };
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogPinGenerateVisible = true;
      this.$nextTick(() => {
        this.$refs["pinRequestForm"].clearValidate();
      });
    },
    approveRequest() {
      this.buttonLoading=true;
      this.$refs["pinRequestForm"].validate(valid => {
        if (valid) {         
          createPinRequest(this.temp).then((data) => {
            this.list.unshift(data.data);
            this.dialogPinGenerateVisible = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading=false;
            this.resetTemp();
          });
        }
      });
      this.buttonLoading=false;
    },
    rejectRequest(row) {
      this.$confirm('Are you sure you want to delete PIN Request?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        deletePinRequest(row.id).then((data) => {          
          this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
          });
          const index = this.list.indexOf(row);
          this.list.splice(index, 1);
        });
      })        
    },    
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
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

.el-select{
  width:100%;
}
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
