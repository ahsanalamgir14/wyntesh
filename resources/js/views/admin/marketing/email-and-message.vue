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
      <el-select v-model="statusFilter" style="width: 140px" placeholder="Status" class="filter-item" @change="handleFilter">
        <el-option  key="1200" label="All" value="all" />
        <el-option  key="1201" label="Active" value="1" />
        <el-option  key="1202" label="Deactive" value="0" />
      </el-select>
      <el-select v-model="listQuery.is_blocked" style="width: 140px" clearable placeholder="Blocked ?" class="filter-item" @change="handleFilter">        
        <el-option  key="1201" label="Blocked" value="blocked" />
        <el-option  key="1202" label="Unblocked" value="unblocked" />
      </el-select>
      <el-select v-model="listQuery.kyc_status" style="width: 140px" clearable placeholder="KYC Status" class="filter-item" @change="handleFilter">        
        <el-option  key="1201" label="Pending" value="pending" />
        <el-option  key="1202" label="Submitted" value="submitted" />
        <el-option  key="1203" label="Rejected" value="rejected" />
        <el-option  key="1204" label="Verified" value="verified" />
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
        v-if="selected.length >= 1"
        type="warning"
        icon="el-icon-chat-line-square"
        @click="handleSendSMS"
      >Send SMS</el-button>

      <el-button
        v-waves
        :loading="downloadLoading"
        v-if="selected.length >= 1"
        class="filter-item"
        type="warning"
        icon="el-icon-message"
        @click="handleSendEmail"
      >Send Email</el-button>

    </div>

    <el-table
      :key="tableKey"
      v-loading="listLoading"
      ref="usersTable"
      :data="list"
      border
      fit
      highlight-current-row
      style="width: 100%;"
      @sort-change="sortChange"
      @selection-change="handleSelectionChange"
    >
      <el-table-column
        type="selection"
        align="center"
        width="55">
      </el-table-column>

      <el-table-column
        label="Sr#"
        prop="id"
        sortable="custom"
        align="center"
        width="70"
        :class-name="getSortClass('id')"
      >
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
       <el-table-column label="ID" width="100px">
        <template slot-scope="{row}">
          <span >{{ row.username }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Name" min-width="120px">
        <template slot-scope="{row}">
          <span>{{ row.name }}</span>
        </template>
      </el-table-column>
     
      <el-table-column label="Contact" width="110px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.contact }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Balance" width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.member?row.member.wallet_balance:'0.00' }}</span>
        </template>
      </el-table-column>

      <el-table-column label="KYC" width="110px" align="center">
        <template slot-scope="{row}">
          <span v-if="row.member.kyc">
            <el-tag v-if="row.member.kyc.verification_status=='verified'" type="success">Verified</el-tag>
            <el-tag v-if="row.member.kyc.verification_status=='pending'" type="warning">Pending</el-tag>
            <el-tag v-if="row.member.kyc.verification_status=='submitted'" type="primary">Submitted</el-tag>
            <el-tag v-if="row.member.kyc.verification_status=='rejected'" type="danger">Rejected</el-tag>
          </span>
        </template>
      </el-table-column>

       <el-table-column label="Status" class-name="status-col" width="100">
        <template slot-scope="{row}">
          <el-tag :type="row.is_active | statusFilter">{{ row.is_active?'Active':'Deactive' }}</el-tag>
        </template>
      </el-table-column>

      <el-table-column label="Is Blocked" class-name="status-col" width="100">
        <template slot-scope="{row}">
          <el-tag :type="row.is_blocked | statusFilter">{{ row.is_blocked?'Yes':'No' }}</el-tag>
        </template>
      </el-table-column>

      <el-table-column label="Joining Date" width="120px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.created_at | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Sponsor" width="110px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.member.sponsor?row.member.sponsor.user.name:'---' }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Sponsor ID" width="110px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.member.sponsor?row.member.sponsor.user.username:'---' }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Parent" width="110px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.member.parent?row.member.parent.user.name:'---' }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Parent ID" width="110px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.member.parent?row.member.parent.user.username:'---' }}</span>
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
    <el-dialog title="Send SMS" width="30%" top="2vh" :visible.sync="dialogSendSMSVisible">
      <el-form ref="smsForm" :rules="smsRules" :model="sms" style="">
        <el-row :gutter="20">
          <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >
            <el-form-item label="Message" prop="message">
              <el-input
                type="textarea"
                :rows="2"
                placeholder="Enter Message"
                v-model="sms.message">
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogSendSMSVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" @click="sendSMS()">
          Send
        </el-button>
      </div>
    </el-dialog>

    <el-dialog title="Send Email" width="30%" top="2vh" :visible.sync="dialogSendEmailsVisible">
      <el-form ref="emailForm" :rules="emailRules" label-position="top" :model="email" style="">
        <el-row :gutter="20">
          <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >
            <el-form-item label="Select Email" prop="email_id">
              <el-select v-model="email.email_id"  clearable  style="width:100%;" filterable placeholder="Select Email">
                <el-option
                  v-for="item in emailList"
                  :key="item.title"
                  :label="item.title"
                  :value="item.id">
                </el-option>
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogSendEmailsVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" @click="sendEmails()">
          Send
        </el-button>
      </div>
    </el-dialog>

  </div>
</template>

<script>
import { fetchList,createUser, updateUser, deleteUser, changeUserStatus, updateExpireDate } from "@/api/admin/users";
import { getAllEmail } from '@/api/admin/emails';
import { sendMassEmail, sendMassSMS } from '@/api/admin/marketing';

import waves from "@/directive/waves";
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";

export default {
  name: "emails-and-message",
  components: { Pagination },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        1: "success",
        draft: "info",
        0: "danger"
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
        limit: 10,
        is_active: 'all',
        sort: "-id"
      },
      statusFilter:'all',
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      selected:[],
      emailList:[],
      email: {
        email_id: undefined,
        users: [],
        is_scheduled:0,
        scheduled_date:undefined,
      },
      sms: {
        message: undefined,
        users: [],
        is_scheduled:0,
        scheduled_date:undefined,
      },
      dialogSendSMSVisible: false,
      dialogSendEmailsVisible:false,
      smsRules: {
        message: [
          { required: true, message: "Message is required", trigger: "blur" }
        ]
      },
      emailRules: {
        email_id: [
          { required: true, message: "Please select email", trigger: "blur" }
        ]
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
      fetchList(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
      getAllEmail().then(response => {
        this.emailList = response.data;
      });
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.listQuery.is_active=this.statusFilter;
      this.getList();
    },
    handleSendSMS(){
      this.resetSMS();
      this.sms.users=this.selected;
      this.dialogSendSMSVisible = true;
      this.$nextTick(() => {
        this.$refs["smsForm"].clearValidate();
      });
    },
    handleSendEmail(){
      this.resetEmail();
      this.email.users=this.selected;
      this.dialogSendEmailsVisible = true;
      this.$nextTick(() => {
        this.$refs["emailForm"].clearValidate();
      });
    },
    sendSMS(){
      this.$refs["smsForm"].validate(valid => {

        if (valid) {
          if(this.sms.users.length<=0){
            this.$message.error('Users are not selected, please select users.');
            return;
          }

          sendMassSMS(this.sms).then((response) => {
            this.dialogSendSMSVisible = false;
            this.resetSMS();
            this.$refs.usersTable.clearSelection();
            this.$notify({
              title: "Success",
              message:response.message,
              type: "success",
              duration: 2000
            })
          }).catch((res)=>{

          });
        }
      });
    }, 
    sendEmails(){
      this.$refs["emailForm"].validate(valid => {
        if (valid) {
          if(this.email.users.length<=0){
            this.$message.error('Users are not selected, please select users.');
            return;
          }
          sendMassEmail(this.email).then((response) => {
            this.dialogSendEmailsVisible = false;
            this.resetEmail();
            this.$refs.usersTable.clearSelection();
            this.$notify({
              title: "Success",
              message:response.message,
              type: "success",
              duration: 2000
            })
          }).catch((res)=>{

          });
        }
      });
    },   
    resetSMS() {
      this.sms = {
        message: undefined,
        users: [],
        is_scheduled:0,
        scheduled_date:undefined,
      };
    },
    resetEmail() {
      this.sms = {
        email_id: undefined,
        users: [],
        is_scheduled:0,
        scheduled_date:undefined
      };

    },
    handleSelectionChange(val) {
      this.selected = val.map(a => a.id);
    },
    createData() {
      this.$refs["smsForm"].validate(valid => {
        if (valid) {
          sendSMS(this.temp).then((response) => {
            this.dialogSendSMSVisible = false;
            this.$notify({
              title: "Success",
              message:response.message,
              type: "success",
              duration: 2000
            })
          }).catch((res)=>{

          });
        }
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
.el-table--medium th, .el-table--medium td {
    padding: 0px 0;
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
.edit-input {
  padding-right: 100px;
}
.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}
</style>
