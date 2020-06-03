<template>
  <div class="app-container">
    <el-row :gutter="40" class="panel-group">

      <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
        <div class="card-panel" v-bind:class="[is_active?'gr2':'gr1']">          
          <div class="card-panel-icon-wrapper icon-message" >
            <i  class="fas card-panel-icon fa-info" style="color: #fff;" ></i>
          </div>        
          <div class="card-panel-description">            
            <div class="card-panel-text">
              Your account is {{is_active?'active':'not active'}}
            </div>
          </div>
        </div>

      </el-col>
    </el-row>
    <div class="filter-container">
      
      <el-date-picker
        v-model="listQuery.date_range"
        class="filter-item"
        type="daterange"
        align="right"
        unlink-panels
        @change="handleFilter"
        format="yyyy-MM-dd"
        value-format="yyyy-MM-dd"
        range-separator="|"
        start-placeholder="Start date"
        end-placeholder="End date"
        :picker-options="pickerOptions">
      </el-date-picker>
      <el-button
        v-waves
        :loading="downloadLoading"
        class="filter-item"
        type="success"
        icon="el-icon-check"
        @click="dialogActivateVisible=true"
      >Activate / Upgrade Account</el-button>
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
      
      <el-table-column label="PIN" width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.pin_number }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Package" width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.package.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Base Amout" width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.base_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Tax Amout" width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.tax_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Total Amout" width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.total_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Used At" width="120px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.used_at | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Note" min-width="150px"align="left">
        <template slot-scope="{row}">
          <span >{{ row.note }}</span>
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

    <el-dialog title="Activate / Upgrade Account" width="40%"  :visible.sync="dialogActivateVisible">
      <el-form ref="formPinApply" :rules="rules" :model="temp">
        <el-form-item label="PIN" prop="pin_number" label-width="120">
          <el-input  @change="handlePinChange" v-model="temp.pin_number" ></el-input>
        </el-form-item>
        <el-form-item label="Package" label-width="120" prop="package">
          <el-input  disabled v-model="temp.package" ></el-input>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogActivateVisible = false">Cancel</el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="handleActivate()">Activate/Upgrade</el-button>
      </span>
    </el-dialog>

  </div>
</template>

<script>
import { getAccountStatus } from "@/api/user/members";
import { getMyUsedPins,checkPin,activatePinAccount } from "@/api/user/pins";
import waves from "@/directive/waves";
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 

export default {
  name: "activateAccount",
  components: { Pagination},
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
      list: null,
      total: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 5,
        status:undefined,
        sort: "-id",
        date_range:''
      },
      is_active:0,
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        pin_number:undefined,
        package:undefined,
      },
      dialogActivateVisible:false,
      rules: {
        pin_number: [
          {  required: true, message: "Enter PIN", trigger: "blur" }
        ],
      },
      downloadLoading: false,
      buttonLoading: false,
      pickerOptions: {
        shortcuts: [{
          text: 'Last week',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
            picker.$emit('pick', [start, end]);
          }
        }, {
          text: 'Last month',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
            picker.$emit('pick', [start, end]);
          }
        }, {
          text: 'Last 3 months',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
            picker.$emit('pick', [start, end]);
          }
        }]
      },
    };
  },
  async created() {
    await this.getAccountStatus();
    this.getList();
  },
  methods: {
    getList() {
      this.listLoading = true;     
      getMyUsedPins(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      }).catch((er)=>{
        this.listLoading = false;
      });
    },
    getAccountStatus(){
      getAccountStatus().then(response => {
        this.is_active = response.is_active
      });
    },
    handlePinChange(){
      checkPin(this.temp.pin_number).then(response => {
        this.temp.package = response.package;
      }).catch((err)=>{
        this.temp.pin_number='';
        this.temp.package='';
      });
    },
    handleActivate(){
      this.$refs["formPinApply"].validate(valid => {        
        if (valid) {
          this.buttonLoading=true;
          activatePinAccount(this.temp).then((response) => {
            this.getList();
            this.getAccountStatus();
            this.resetTemp();
            this.dialogActivateVisible = false;
            this.buttonLoading=false;
            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            })

          }).catch((err)=>{
            this.buttonLoading=false;
          });
        }
      });
    },
    resetTemp() {
      this.temp = {
        pin_number:undefined,
        package:undefined,
      };
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

<style lang="scss" scoped>
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
.edit-input {
  padding-right: 100px;
}
.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}

.panel-group {
  margin-top: 18px;

  .card-panel-col {
    margin-bottom: 32px;
  }

  .card-panel {
    height: 108px;
    cursor: pointer;
    font-size: 12px;
    position: relative;
    overflow: hidden;
    color: #fff;
    
    border-radius: 10px;
    border:1px solid #ccc;

    .card-panel-icon-wrapper {
      float: left;
      margin: 10px 0 0 10px;
      padding: 5px;
      transition: all 0.38s ease-out;
      border-radius: 6px;
    }

    .card-panel-icon {
      float: left;
      font-size: 35px;
      border-style: solid;
      border-width: thin;
      padding:5px;
      height: 35px;
      width: 45px;
    }
    @media (min-width:550px) {
      .card-panel-description {
        float: right;
        font-weight: bold;
        margin-top: 15px;
        margin-right: 10px;
        width: 90%;

        .card-panel-text {
          line-height: 25px;
          color: #fff;
          font-size: 16px;
          display: block;
          margin-top: 0px;
        }

        .card-panel-num {
          font-size: 25px;
          float:right;
          display: block;
        }
      }
    }
  }
}

@media (max-width:550px) {
  .card-panel{
    .card-panel-description {
        font-weight: bold;
          margin: 5px auto !important;
          float: none !important;
          text-align: center;
        .card-panel-text {
          line-height: 20px;
          color: rgba(0, 0, 0, 0.45);
          font-size: 10px;
        }

        .card-panel-num {
          display: block;
          font-size: 20px;
        
        }
      }
  }

  .card-panel-icon-wrapper {
    float: none !important;
    margin: 0 !important;

    svg {
      display: block;
      margin: 5px auto !important;
      float: none !important;
    }
  }
}

.gr1{
  background: rgb(230,101,101);
  background: linear-gradient(60deg, rgba(230,101,101,1) 45%, rgba(15,163,217,1) 100%);
}

.gr2{
  background: rgb(52,158,77);
  background: linear-gradient(60deg, rgba(52,158,77,1) 42%, rgba(99,237,247,1) 100%);
}

</style>
