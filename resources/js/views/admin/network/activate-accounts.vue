<template>
  <div class="app-container">
    
    <div class="filter-container">
      <el-input
        v-model="listQuery.member_id"
        placeholder="Member ID"
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
        <el-form-item prop="member_id" label="Member ID">               
          <el-input v-model="temp.member_id" v-on:blur="handleCheckMemberCode()" name="member_id" type="text" auto-complete="on" placeholder="Member ID" />
        </el-form-item>
        <el-form-item prop="member_name" label="Member Name">
          
          <el-input v-model="temp.member_name" disabled name="member_name" type="text" auto-complete="on" placeholder="Member Name." />
        </el-form-item>


        <el-form-item label="PIN" prop="pin_number" label-width="120">
          <el-input  @change="handlePinChange" v-model="temp.pin_number" ></el-input>
        </el-form-item>
        <el-form-item label="Package" label-width="120" prop="package">
          <el-input  disabled v-model="temp.package" ></el-input>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogActivateVisible = false">Cancel</el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading"  @click="handleActivate()">Confirm</el-button>
      </span>
    </el-dialog>

  </div>
</template>

<script>
import { getMemberUsedPins,checkPin,activatePinAccount } from "@/api/admin/pins";
import { checkMemberCode } from "@/api/admin/members";
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
      listLoading: false,
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
        member_name:undefined,
        member_id:undefined,
      },
      dialogActivateVisible:false,
      rules: {
        member_id: [
          {  required: true, message: "Member ID is required.", trigger: "blur" }
        ],
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
  },
  methods: {
    getList() {
      this.listLoading = true;     
      getMemberUsedPins(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      }).catch((err)=>{
        this.list=[];
        this.listLoading = false;
      });
    },
    handleCheckMemberCode(){
      checkMemberCode(this.temp.member_id).then(response => {
        this.temp.member_name = response.data.name;
      }).catch((err)=>{
        this.temp.member_name='';
        this.temp.member_id='';
      });
    },
    handlePinChange(){
      if(this.temp.pin_number){
        checkPin(this.temp.pin_number).then(response => {
          this.temp.package = response.package;
        }).catch((err)=>{
          this.temp.pin_number='';
          this.temp.package='';
        });
      }
      
    },
    handleActivate(){
      this.$refs["formPinApply"].validate(valid => {        
        if (valid) {
          this.buttonLoading=true;
          activatePinAccount(this.temp).then((response) => {
            let temp = Object.assign({}, this.temp);
            this.listQuery.member_id=temp.member_id;
            this.getList();          
            this.resetTemp();
            this.buttonLoading=false;
            this.dialogActivateVisible = false;
            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            })

          }).catch((res)=>{
            this.buttonLoading=false;
          });
        }
      });
    },
    resetTemp() {
      this.temp = {
        pin_number:undefined,
        package:undefined,
        member_name:undefined,
        member_id:undefined,
      };
    },
    handleFilter() {
      this.listQuery.page = 1;
      if(!this.listQuery.member_id){
         this.$message.error('Member Id is reqruired.');
         return;
      }
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


</style>
