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
        style="margin-left: 10px;float:right;"
        type="success"   
         @click="handleCreate"     
        ><i class="fas fa-plus"></i> Generate Pins</el-button>
      <br>
      
      <el-select v-model="listQuery.status" @change="handleFilter"  clearable class="filter-item" style="width:200px;" filterable placeholder="Select Status">
        <el-option
          v-for="item in pin_statuses"
          :key="item.name"
          :label="item.name"
          :value="item.name">
        </el-option>
      </el-select>

      <el-date-picker
        v-model="listQuery.used_at_date_range"
        class="filter-item"
        type="daterange"
        align="right"
        unlink-panels
        @change="handleFilter"
        format="yyyy-MM-dd"
        value-format="yyyy-MM-dd"
        range-separator="|"
        start-placeholder="Used at start date"
        end-placeholder="Used at end date"
        :picker-options="pickerOptions">
      </el-date-picker>
      <el-date-picker
        v-model="listQuery.allocated_at_date_range"
        class="filter-item"
        type="daterange"
        align="right"
        unlink-panels
        @change="handleFilter"
        format="yyyy-MM-dd"
        value-format="yyyy-MM-dd"
        range-separator="|"
        start-placeholder="Allocation start date"
        end-placeholder="Allocation end date"
        :picker-options="pickerOptions">
      </el-date-picker> 
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

    <el-dialog :title="dialogTitle" width="60%" top="30px"  :visible.sync="dialogPinGenerateVisible">
      <el-form ref="pinGenerateForm" :rules="rules" :model="temp"  >
        <el-row :gutter="20">
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item label="Package" prop="package_id">
              <br>
              <el-select @change="handlePackageChange()" v-model="temp.package_id"  filterable placeholder="Select Package">
                <el-option
                  v-for="item in packages"
                  :key="item.name"
                  :label="item.name"
                  :value="item.id">
                </el-option>
              </el-select>
            </el-form-item> 
            <el-form-item label="Member ID" prop="member_id">
              <el-input  v-on:blur="handleCheckMemberCode()" v-model="temp.member_id" />
            </el-form-item>
            <el-form-item label="Member Name" prop="member_name">
              <el-input  disabled v-model="temp.member_name" />
            </el-form-item>
            <el-form-item label="Quantity" prop="quantity">
              <el-input type="number" min=0 v-model="temp.quantity" />
            </el-form-item>
          </el-col>
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >              
            <el-form-item label="Base Amount" prop="base_amount">
              <el-input type="number" min=0 v-model="temp.base_amount"  @change="calculateFinalPrice()" />
            </el-form-item>
            <el-form-item label="Tax Percentage" prop="tax_percentage">
              <el-input type="number" min=0 v-model="temp.tax_percentage"  @change="calculateFinalPrice()" />
            </el-form-item>
            <el-form-item label="Tax Amount" prop="tax_amount">
              <el-input type="number" min=0 v-model="temp.tax_amount" />
            </el-form-item>
            <el-form-item label="Net Amount" prop="total_amount">
              <el-input type="number" min=0 v-model="temp.total_amount" />
            </el-form-item>

            <el-form-item label="Note" prop="note">
              <el-input
                type="textarea"
                v-model="temp.note"
                :rows="2"
                placeholder="Please Enter Note">
              </el-input>
            </el-form-item>
          </el-col>
         
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogPinGenerateVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading"  @click="generatePins()">
          Generate
        </el-button>
      </div>
    </el-dialog>

  </div>
</template>

<script>
import { fetchAllPins, generatePin } from "@/api/admin/pins";
import { getPackages,getStatuesAll  } from "@/api/admin/config";
import { checkMemberCode } from "@/api/admin/members";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";

export default {
  name: "all-pins",
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
      packages:[],
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        id:undefined,
        package_id: undefined,
        quantity: 0,
        base_amount:0,
        tax_percentage: 0,
        tax_amount: 0,
        total_amount:0,
        note:undefined, 
        member_id:undefined,
        member_name:undefined,
      },

      dialogPinGenerateVisible:false,
      dialogTitle:'',
      is_create:true,
      pin_statuses:[],
      rules: {
        package_id: [{ required: true, message: 'Package is required', trigger: 'blur' }],
        quantity: [{  required: true, message: 'Quantity is required', trigger: 'blur' }],
        base_amount: [{  required: true, message: 'Base amount is required', trigger: 'blur' }],
        tax_percentage: [{  required: true, message: 'Tax percentage is required', trigger: 'blur' }],
        tax_amount: [{  required: true, message: 'Tax amount is required', trigger: 'blur' }],
        total_amount: [{  required: true, message: 'Total amount is required', trigger: 'blur' }],
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
      getStatuesAll('pins').then(response => {
        this.pin_statuses = response.data;
      });
    },    
    resetTemp() {
      this.temp = {
        id:undefined,
        package_id: undefined,
        quantity: undefined,
        amount:undefined,
        tax_percentage: undefined,
        base_amount:undefined,
        tax_amount: undefined,
        total_amount:undefined,
        note:undefined,
        member_id:undefined,
        member_name:undefined,
      };
    },
    async handlePackageChange(){
      
      let packg=await this.packages.filter((pack)=>{
        return pack.id==this.temp.package_id;
      })[0];
      let tempPack = await Object.assign({}, packg);

      this.temp.base_amount=tempPack.base_amount;
      this.temp.tax_percentage=tempPack.gst_rate;
      this.temp.tax_amount=tempPack.gst_amount;
      this.temp.total_amount=tempPack.net_amount;



    },
    handleCheckMemberCode(){
      if(this.temp.member_id){
        checkMemberCode(this.temp.member_id).then((response) => {
          this.temp.member_name=response.data.name;     
          this.temp.owned_by=response.data.member.id;     
        }).catch((error)=>{
          this.temp.member_name='';
          this.temp.member_id='';
        });
      }            
    },
    calculateFinalPrice(){
      let temp=Object.assign({}, this.temp);
      if(temp.tax_percentage != undefined && temp.tax_percentage != null){
        if(temp.tax_percentage == 0){
          this.temp.tax_amount=0;
          this.temp.total_amount=temp.base_amount;
        }else{
          let gst=(temp.tax_percentage*temp.base_amount)/100;
          gst=Math.floor(gst);
          this.temp.tax_amount=gst;
          this.temp.total_amount=parseInt(temp.base_amount)+gst;
        }        
      }
    },
    handleCreate() {
      this.resetTemp();
      this.is_create = true;
      this.dialogTitle="Generate Pins";
      this.dialogPinGenerateVisible = true;
      this.$nextTick(() => {
        this.$refs["pinGenerateForm"].clearValidate();
      });
    },
    generatePins() {      
      this.$refs["pinGenerateForm"].validate(valid => {
        if (valid) {
        this.buttonLoading=true;         
          generatePin(this.temp).then((data) => {
            this.dialogPinGenerateVisible = false;
            this.buttonLoading=false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.getList();
            this.resetTemp();
          }).catch((err)=>{
            this.buttonLoading=false;
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
