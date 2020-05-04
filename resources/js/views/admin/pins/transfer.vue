<template>
  <div class="app-container">
    <div class="filter-container">
      
      <el-input
        v-model="listQuery.search"
        placeholder="Member / PIN"
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

      <el-select v-model="listQuery.is_owned" @change="handleFilter"  clearable class="filter-item" style="width:200px;" filterable placeholder="Owner Filter">
        <el-option label="Owned" value="Owned"></el-option>
        <el-option label="Not Owned" value="Not Owned"></el-option>
      </el-select>

      <el-button
        v-waves
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >Search</el-button>
      <el-button
        v-waves
        :loading="downloadLoading"
        class="filter-item"
        type="primary"
        icon="el-icon-download"
        @click="handleDownload"
      >Export</el-button>

      <el-button
        class="filter-item"
        style="margin-left: 10px;"
        type="warning"
        icon="el-icon-sort"
        v-if="pinList.length >= 1"
        @click="handlePinTansfer"
      >Transfer Pins</el-button>
           
    </div>
    <el-table
      :key="tableKey"
      v-loading="listLoading"
      ref="pinTransferTable"
      :data="list"
      border
      fit
      highlight-current-row
      style="width: 100%;"
      @selection-change="handleSelectionChange"
      @sort-change="sortChange"
      >

      <el-table-column
        label="ID"
        prop="id"
        sortable="custom"
        align="center"
        width="80"
        :class-name="getSortClass('id')"
        @selection-change="handleSelectionChange"
        >
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column
        align="center"
        type="selection"
        width="55">
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
      <el-table-column label="Status" width="150px" >
        <template slot-scope="{row}">
          <el-tag :type="row.status | statusFilter">{{row.status}}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="Used by" width="130px" >
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

    <el-dialog title="Transfer Pins to Member" width="40%" center :visible.sync="dialogPinTransferVisible" style="height: auto;margin: 0 auto;">
      <el-form ref="pinTransferForm" :rules="pinTransferRules"  :model="temp" style="width: 70%;margin: 0 auto;">
        <el-form-item label="Member ID" prop="member_id">
          <el-input  v-on:blur="handleCheckMemberId()" v-model="temp.member_id" />
        </el-form-item>
        <el-form-item label="Member Name" prop="member_name">
          <el-input  disabled v-model="temp.member_name" />
        </el-form-item>
        <el-input
          type="textarea"
          v-model="temp.note"
          :rows="2"
          placeholder="Please Enter Note">
        </el-input>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogPinTransferVisible = false">Cancel</el-button>
        <el-button type="primary" @click="transferPins()">Confirm</el-button>
      </span>
    </el-dialog>

  </div>
</template>

<script>
import { fetchNotUsedPins, transferPinsToMember } from "@/api/admin/pins";
import { getPackages,  } from "@/api/user/config";
import { checkMemberCode } from "@/api/admin/members";
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
        'Not Used': "success",
        Used: "info",
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
        limit: 10,
        search:undefined,
        package_id: undefined,
        sort: "+id"
      },
      pinList:[],
      packages:[],
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        member_id:undefined,
        member_name: undefined,   
        note:undefined,

      },
      pinTransferRules: {
        member_id: [{ required: true, message: 'Member Id is required', trigger: 'blur' }]
      },
      dialogPinTransferVisible:false,
      dialogTitle:'',
      is_create:true,      
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
      fetchNotUsedPins(this.listQuery).then(response => {
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
    handleCheckMemberId(){
      if(this.temp.member_id){
        checkMemberCode(this.temp.member_id).then((response) => {
          this.temp.member_name=response.data.name;    
        }).catch((error)=>{
          this.temp.member_name='';
        });
      }            
    },
    handleSelectionChange(val) {        
        this.pinList = val.map(a => a.id);        
    },
    handlePinTansfer() {
      this.dialogPinTransferVisible = true;
      this.$nextTick(() => {
        this.$refs["pinTransferForm"].clearValidate();
      });      
    },
    transferPins(){
      this.$refs["pinTransferForm"].validate(valid => {
        if (valid) {
          let tempData={
            'member_id':this.temp.member_id,
            'pins':this.pinList,
            'note':this.temp.note
          };
          transferPinsToMember(tempData).then((response) => {
            this.pinList=[];
            this.getList();
            this.dialogPinTransferVisible = false;
            this.temp.member_id='';
            this.temp.member_name='';
            this.temp.note='';
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
    },
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "Sr.No",
          "PIN",
          "Owner",
          "Package",
          "Base amount",
          "Tax %",
          "Tax Amount",
          "Total Amount",
          "Status",
          "Used by",
          "Used at",
          "Allocation Date",
          "Note",
          "Created At",
        ];
        const filterVal = [
          "id",
          "pin_number",
          "owned_by",
          "package_id",
          "base_amount",
          "tax_percentage",
          "tax_amount",
          "total_amount",
          "status",
          "used_by",
          "used_at",
          "allocated_at",
          "note",
          "created_at",
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "pins"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "timestamp") {
            return parseTime(v[j]);
          } else if(j=="owned_by"){
            return v.owner.user.username
          }else if(j=="package_id"){
            return v.package.name
          }else if(j=="used_by"){
            return v.user?v.user.user.username:'';
          }else {
            return v[j];
          }
        })
      );
    },
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
