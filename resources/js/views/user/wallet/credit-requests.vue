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
        ><i class="fas fa-plus"></i> Create Wallet Credit Request</el-button>      
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
      <!-- <el-table-column label="Actions" align="center" width="90" class-name="small-padding">        
        <template slot-scope="{row}">
          <el-tooltip content="Delete" placement="right" effect="dark">
            <el-button
              circle
              type="danger"
              icon="el-icon-delete"
              @click="deleteData(row)"
              ></el-button>
          </el-tooltip>
        </template>
      </el-table-column> -->
      
      <el-table-column label="Payment Mode" width="120px" >
        <template slot-scope="{row}">
          <span>{{ row.payment_mode.name }}</span>
        </template>
      </el-table-column>
    
      <el-table-column label="Amount" width="120px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Image" width="120px" align="right">
        <template slot-scope="{row}">
          <a  v-if="row.image" :href="row.image" target="_blank">View image.</a>
        </template>
      </el-table-column>
     
      <el-table-column label="Reference No." width="120px" >
        <template slot-scope="{row}">
          <span>{{ row.reference }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Bank Name" width="150px" >
        <template slot-scope="{row}">
          <span>{{ row.bank.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Payment Status" width="150px" >
        <template slot-scope="{row}">
          <span>{{ row.payment_status }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Status" width="150px" >
        <template slot-scope="{row}">
          <el-tag :type="row.status | statusFilter">{{row.status}}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="Note" min-width="150px" >
        <template slot-scope="{row}">
          <span>{{ row.note }}</span>
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

    <el-dialog :title="dialogTitle" width="80%" top="30px"  :visible.sync="dialogWalletCreditRequestVisible">
      <el-form ref="walletCreditRequestForm" :rules="rules" :model="temp"  >
        <el-row :gutter="20">
          <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >
             
            <el-form-item label="Amount" prop="amount">
              <el-input type="number" min=0 v-model="temp.amount" />
            </el-form-item>
            <el-form-item label="Payment Mode" prop="payment_mode">
              <el-select v-model="temp.payment_mode" filterable placeholder="Select Payment Mode">                
                <el-option
                  v-for="item in paymentModes"
                  :key="item.name"
                  :label="item.name"
                  :value="item.id">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="Payment reference" prop="reference">
              <el-input v-model="temp.reference" />
            </el-form-item>
             <el-form-item label="Note" prop="note">
              <el-input
                type="textarea"
                v-model="temp.note"
                :rows="2"
                placeholder="Please Enter note">
              </el-input>
            </el-form-item>
          </el-col>
          <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >                          
            <el-form-item label="Bank" prop="bank_id">
              <el-select v-model="temp.bank_id" @change="selectBank()" clearable filterable placeholder="Select Bank">
                <el-option
                  v-for="item in banks"
                  :key="item.name"
                  :label="item.name"
                  :value="item.id">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="Branch" prop="branch_name">
              <el-input disabled v-model="temp.branch_name" />
            </el-form-item>
            <el-form-item label="IFSC" prop="ifsc">
              <el-input disabled v-model="temp.ifsc" />
            </el-form-item>
            <el-form-item label="Account Type" prop="account_type">
              <el-input disabled v-model="temp.account_type" />
            </el-form-item>
            <el-form-item label="Account Holder name" prop="account_holder_name">
              <el-input disabled v-model="temp.account_holder_name" />
            </el-form-item>
            <el-form-item label="Account Number" prop="account_number">
              <el-input disabled v-model="temp.account_number" />
            </el-form-item>           
          </el-col>
          <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >                          
            <el-form-item  prop="image">
              <label for="Image">Image</label>
              <el-upload
                class="avatar-uploader"
                action="#"
                ref="upload"
                :show-file-list="true"
                :auto-upload="false"
                :on-change="handleImageChange"
                :on-remove="handleImageRemove"
                :limit="1"
                :file-list="imagefileList"
                :on-exceed="handleExceed"
                accept="image/png, image/jpeg">
                <i class="el-icon-plus avatar-uploader-icon"></i>
              </el-upload>                   
            </el-form-item>
          </el-col>         
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogWalletCreditRequestVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="dialogStatus==='create'?createData():updateData()">
          Confirm
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { fetchMyCreditRequests, createCreditRequest} from "@/api/user/wallet";
import { getPaymentModes, getBankPartners } from "@/api/user/config";

import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";

export default {
  name: "pending-pin-requests",
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
        limit: 10,
        search:undefined,
        sort: "+id"
      },
      imagefile:undefined,
      imagefileList:[],
      paymentModes:[],
      banks:[],
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        id:undefined,
        amount:undefined,
        payment_mode:undefined,
        reference:undefined,
        bank_id:undefined,
        note:undefined,
      },

      dialogWalletCreditRequestVisible:false,
      dialogStatus: "",
      dialogTitle:'',
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        amount: [{  required: true, message: 'Amount is required', trigger: 'blur' }],
        payment_mode: [{  required: true, message: 'Payment mode is required', trigger: 'blur' }],
        reference: [{  required: true, message: 'Payment reference no is required', trigger: 'blur' }],
        bank_id: [{  required: true, message: 'Select Bank', trigger: 'blur' }],
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
      fetchMyCreditRequests(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    handleImageChange(f, fl){     
      if(fl.length > 1){
        fl.shift()  
      }      
      this.imagefile=f.raw      
    },
    handleImageRemove(file, fileList) {
       this.adharfile=undefined;
       this.imagefileList=[];
    },
    getConfig() {
    
      getPaymentModes().then(response => {
        this.paymentModes = response.data;
      });
      getBankPartners().then(response => {
        this.banks = response.data;
      });
    },
    resetTemp() {
      this.temp = {
        id:undefined,
        amount:undefined,
        payment_mode:undefined,
        reference:undefined,
        bank_id:undefined,
        note:undefined,
      };
    },
    selectBank(){
      let bank_id=this.temp.bank_id;
      if(bank_id){
        let bank=this.banks.map((bank)=>{
          if(bank.id==bank_id){
            return bank;  
          }else{
            return false;
          }
        })[0];
        console.log(bank);
        this.temp.branch_name=bank.branch_name;
        this.temp.ifsc=bank.ifsc;
        this.temp.account_type=bank.account_type;
        this.temp.account_holder_name=bank.account_holder_name;
        this.temp.account_number=bank.account_number;
      }else{
        this.temp.branch_name=undefined;
        this.temp.ifsc=undefined;
        this.temp.account_type=undefined;
        this.temp.account_holder_name=undefined;
        this.temp.account_number=undefined;
      }
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogWalletCreditRequestVisible = true;
      this.dialogTitle="Create Wallet Credit Request"
      this.$nextTick(() => {
        this.$refs["walletCreditRequestForm"].clearValidate();
      });
    },
    createData() {
      this.$refs["walletCreditRequestForm"].validate(valid => {
        if (valid) {     
          this.buttonLoading=true;
          var form = new FormData();
          let form_data=this.temp;

          for ( var key in form_data ) {
            if(form_data[key] !== undefined && form_data[key] !== null){              
                form.append(key, form_data[key]);               
            }
          }

          form.append('image', this.imagefile);

          createCreditRequest(form).then((data) => {
            this.list.unshift(data.data);
            this.dialogWalletCreditRequestVisible = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.imagefile=undefined
            this.imagefileList=[];
            this.buttonLoading=false;
            this.resetTemp();
          }).catch((err)=>{
            this.buttonLoading=false;
          });
        }
      });
    },
    deleteData(row) {
      this.$confirm('Are you sure you want to delete Wallet Credit Request?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        this.buttonLoading=true;
        deletePinRequest(row.id).then((data) => {
          this.dialogWalletCreditRequestVisible = false;
          this.buttonLoading=false;
          this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
          });
          const index = this.list.indexOf(row);
          this.list.splice(index, 1);
        }).catch((err)=>{
          this.buttonLoading=false;
        });
      })        
    },
    handleExceed(files, fileList) {
      this.$message.warning(`You can not select more than one file, please remove first.`);
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
