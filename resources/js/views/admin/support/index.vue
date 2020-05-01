<template>
  <div class="app-container">    
   
    <el-tabs :tab-position="tabPosition" style="height: 100%;" @tab-click="handleTabClick">
      <el-tab-pane label="Opened Tickets" >
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
            label="Ticket ID"
            prop="id"
            sortable="custom"
            align="center"
            width="110"
            :class-name="getSortClass('id')"
          >
            <template slot-scope="{row}">
              <span>#{{ row.id }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Actions" align="center" width="130" class-name="small-padding">
            <template slot-scope="{row}">
              <el-tooltip class="item" effect="dark" content="View coversation" placement="top-start">
                <el-button icon="el-icon-view"
                  :loading="buttonLoading"
                  circle type="success" @click="handleOpenCoversation(row)">
                </el-button>
              </el-tooltip>
              <el-tooltip class="item" effect="dark" content="Close Ticket" placement="top-start">
                <el-button icon="el-icon-check"
                  :loading="buttonLoading"
                  circle type="warning" @click="handleCloseTicket(row)">
                </el-button>
              </el-tooltip>
             
            </template>
          </el-table-column>
          <el-table-column label="User ID" width="110px">
            <template slot-scope="{row}">
              <span>{{ row.user?row.user.username:'' }}</span>
            </template>
          </el-table-column>
          <el-table-column label="User Name" width="110px">
            <template slot-scope="{row}">
              <span>{{ row.user?row.user.name:'' }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Subject" min-width="150px">
            <template slot-scope="{row}">
              <span>{{ row.subject }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Status" width="110px">
            <template slot-scope="{row}">
              <span>{{ row.status | uppercaseFirst }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Assigned to" width="110px">
            <template slot-scope="{row}">
              <span>{{ row.assigned?row.assigned.name:'' }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Created At" width="150px" align="center">
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

      </el-tab-pane>
      <el-tab-pane label="Closed">
        <div class="filter-container">
            <el-input
              v-model="listClosedQuery.search"
              placeholder="Search Records"
              style="width: 200px;"
              class="filter-item"
              @keyup.enter.native="handleClosedFilter"
            />
            <el-button
              v-waves
              class="filter-item"
              type="primary"
              icon="el-icon-search"
              @click="handleClosedFilter"
            >Search</el-button>
        </div>
        <el-table
          :key="tableKey"
          v-loading="listLoading"
          :data="closedlist"
          border
          fit
          highlight-current-row
          style="width: 100%;"
          @sort-change="sortClosedChange"
          >
          <el-table-column
            label="Ticket ID"
            prop="id"
            sortable="custom"
            align="center"
            width="110"
            :class-name="getSortClass('id')"
          >
            <template slot-scope="{row}">
              <span>#{{ row.id }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Actions" align="center" width="130" class-name="small-padding">
            <template slot-scope="{row}">
              <el-tooltip class="item" effect="dark" content="View coversation" placement="top-start">
                <el-button icon="el-icon-view"
                  circle type="success" @click="handleOpenCoversation(row)">
                </el-button>
              </el-tooltip>
            </template>
          </el-table-column>
          <el-table-column label="User ID" width="110px">
            <template slot-scope="{row}">
              <span>{{ row.user?row.user.username:'' }}</span>
            </template>
          </el-table-column>
          <el-table-column label="User Name" width="110px">
            <template slot-scope="{row}">
              <span>{{ row.user?row.user.name:'' }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Subject" min-width="150px">
            <template slot-scope="{row}">
              <span>{{ row.subject }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Status" width="110px">
            <template slot-scope="{row}">
              <span>{{ row.status | uppercaseFirst }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Assigned to" width="110px">
            <template slot-scope="{row}">
              <span>{{ row.assigned?row.assigned.name:'' }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Created At" width="150px" align="center">
            <template slot-scope="{row}">
              <span>{{ row.created_at | parseTime('{y}-{m}-{d}') }}</span>
            </template>
          </el-table-column>
        </el-table>

        <pagination
          v-show="closedtotal>0"
          :total="closedtotal"
          :page.sync="listClosedQuery.page"
          :limit.sync="listClosedQuery.limit"
          @pagination="getClosedList"
        />

      </el-tab-pane>    
    </el-tabs>

    <el-drawer
      title="I am the title"
      :size="ticketConversationDrawerSize"
      :visible.sync="showTicketConversations"
      direction="rtl" :with-header="false">
      <div style="padding: 20px 20px 20px 20px; ">
        <el-form ref="formCreateConversation" :rules="createConversationRules" :model="temp" label-position="top"  v-if="temp.status!='closed'">          
           
            <el-form-item  label="Send Message" prop="message">
              <tinymce v-model="temp.message"  :imageUploadButton="false" menubar="format" :toolbar="tools" id="conversationMessage" ref="conversationMessage" :value="temp.message" :height="50" />
            </el-form-item>
            <el-form-item  prop="file"  label="Attachment">
                <el-row>
                  <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                     <el-upload
                      class="avatar-uploader"
                      action="#"
                       ref="upload"
                      :show-file-list="true"
                      :auto-upload="false"
                      :on-change="handleChange"
                      :on-remove="handleRemove"
                      :limit="1"
                      :file-list="fileList"
                      :on-exceed="handleExceed"
                    >
                      <el-button size="small" type="primary">Select file.</el-button>
                    </el-upload>
                  </el-col>
                  <el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                    <el-button
                      style="float:right;margin-bottom: 50px;"
                      v-waves
                      :loading="buttonLoading"
                      type="success"
                      @click="handleCreateConversation"
                      > Submit
                    </el-button>
                  </el-col>
                </el-row>                                
            </el-form-item>
           
        </el-form>

        <div class="user-activity">
            <div class="post" v-for="conversation in conversations">
              <div class="user-block">
                <img v-if="!conversation.is_agent"
                  class="img-circle"
                  :src="avatar"
                  alt="user image"
                >
                <img v-if="conversation.is_agent"
                  class="img-circle"
                  :src="support"
                  alt="user image"
                >

                <span class="username text-muted">
                  <a href="#">{{conversation.from_user?conversation.from_user.name:''}}</a>                 
                </span>
                <span class="description">{{conversation.created_at}}</span>
              </div>
              <p>
                <span v-html="conversation.message"></span>
              </p>
              <ul class="list-inline" v-if="conversation.attachment">
                <li>
                  <a :href="'/api/download-file?file='+conversation.attachment" class="link-black text-sm">
                    <i class="el-icon-download" /> Download Attachment
                  </a>
                </li>
              </ul>              
            </div>
        </div>
      </div>
    </el-drawer>

  </div>
</template>

<script>
import { fetchOpenedList, fetchClosedList, openSupportTicket, closeUserSupportTicket, getAdminConversations,
  addAdminConversationMessage,
 } from "@/api/user/support";
import avatar from '@/assets/images/avatar.png'
import support from '@/assets/images/support.png'

import waves from "@/directive/waves"; 
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 
import axios from "axios";
import Tinymce from '@/components/Tinymce'

export default {
  name: "SupportTickets",
  components: { Pagination,Tinymce },
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
      tools: [''],
      avatar:avatar,
      support:support,
      showTicketConversations:false,
      ticketConversationDrawerSize:"40%",
      tableKey: 0,
      list: null,
      total: 0,
      closedlist: null,
      closedtotal: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 5,
        search: undefined,
        sort: "-id"
      },
      listClosedQuery: {
        page: 1,
        limit: 5,
        search: undefined,
        sort: "-id"
      },
      conversations:[],
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        id:undefined,
        subject: undefined,
        message: undefined,
      },
      fileList:[],
      file:undefined,
      tabPosition:'left',
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      createConversationRules:{
        message: [
          { required: true, message: "Message is required", trigger: "blur" }
        ],
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
    if(window.screen.width <= '550'){
      this.ticketConversationDrawerSize='90%';
      this.tabPosition='top';
    }
  },
  methods: {
    handleTabClick(tab, event) {
      if(tab.label=='Closed'){
        this.getClosedList();
      }
      if(tab.label=='Opened Tickets'){
        this.getList();
      }
    },
    handleChange(f, fl){     
      if(fl.length > 1){
        fl.shift()  
      }      
      this.file=f.raw      
    },
    handleRemove(file, fileList) {
       this.file=undefined;
       this.fileList=[];
    },
    handleExceed(files, fileList) {
      this.$message.warning(`You can not select more than one file, please remove first.`);
    },
    resetTemp(){
      this.temp={
         id:undefined,
        subject: undefined,
        message: undefined,
      };
      this.file=undefined;
      this.fileList=[];
    },
    getList() {
      this.listLoading = true;
      fetchOpenedList(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });     
    },
    getClosedList() {
      this.listLoading = true;
      fetchClosedList(this.listQuery).then(response => {
        this.closedlist = response.data.data;
        this.closedtotal = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });     
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
    handleClosedFilter() {
      this.listClosedQuery.page = 1;
      this.getClosedList();
    },
    sortChange(data) {
      const { prop, order } = data;
      if (prop === "id") {
        this.sortByID(order);
      }
    },
    sortClosedChange(data) {
      const { prop, order } = data;
      if (prop === "id") {
        this.sortClosedByID(order);
      }
    },
    handleOpenCoversation(row){
      this.resetTemp();
      this.temp=row;
      getAdminConversations(row.id).then(response => {
        this.conversations = response.data;
        this.showTicketConversations=true;
      });

    },
    handleCreateConversation(){
      
      this.$refs["formCreateConversation"].validate(valid => {
        if (valid) {
          this.buttonLoading=true;
          var form = new FormData();
          let form_data=this.temp;

          for ( var key in form_data ) {
            if(form_data[key] !== undefined && form_data[key] !== null){
              form.append(key, form_data[key]);
            }
          }

          form.append('attachment', this.file);

          addAdminConversationMessage(form).then((data) => {
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            
            getAdminConversations(this.temp.id).then(response => {
              this.conversations = response.data;
            });

              this.buttonLoading=false;
              this.$nextTick(() => {
                this.$refs["formCreateConversation"].clearValidate();
                this.$refs.conversationMessage.setContent("");
              });
          });
        }
      });
    },
    handleCloseTicket(row){
      this.$confirm('Are you sure you want to close this ticket?')
        .then(_ => {
          this.buttonLoading=true;
          const postData={
            id:row.id
          };

          closeUserSupportTicket(postData).then((data) => {
            this.getList();
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading=false;
          });
        })
          
    },
   
    sortByID(order) {
      if (order === "ascending") {
        this.listQuery.sort = "+id";
      } else {
        this.listQuery.sort = "-id";
      }
      this.handleFilter();
    },
    sortClosedByID(order) {
      if (order === "ascending") {
        this.listClosedQuery.sort = "+id";
      } else {
        this.listClosedQuery.sort = "-id";
      }
      this.handleClosedFilter();
    },
    getSortClass: function(key) {
      const sort = this.listQuery.sort;
      return sort === `+${key}`
        ? "ascending"
        : sort === `-${key}`
        ? "descending"
        : "";
    },    
    getClosedSortClass: function(key) {
      const sort = this.listClosedQuery.sort;
      return sort === `+${key}`
        ? "ascending"
        : sort === `-${key}`
        ? "descending"
        : "";
    }
  }
};
</script>

<style scoped lang="scss">
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
