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
      <el-select v-model="listQuery.is_attended" style="width: 140px" placeholder="Status" class="filter-item" @change="handleFilter">
        <el-option  key="1200" label="All" value="all" />
        <el-option  key="1201" label="Attended" value="1" />
        <el-option  key="1202" label="Not Attended" value="0" />
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
      <el-table-column label="Actions" align="center" width="150" class-name="small-padding">
        <template slot-scope="{row}">

          <el-button icon="el-icon-turn-off"
            circle v-if="row.is_attended!=1" type="info" @click="handleModifyStatus(row,1)">
          </el-button>
          <el-button icon="el-icon-open" circle v-if="row.is_attended!=0" type="success" @click="handleModifyStatus(row,0)">
          </el-button>
          <el-button
            type="danger"
            icon="el-icon-delete"
            circle
            :loading="buttonLoading"
            @click="handleDelete(row)"
          ></el-button>
        </template>
      </el-table-column>
      <el-table-column label="Name" width="110px">
        <template slot-scope="{row}">
          <span>{{ row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Contact" width="110px">
        <template slot-scope="{row}">
          <span>{{ row.contact }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Email" width="170px">
        <template slot-scope="{row}">
          <span>{{ row.email }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Subject" width="250px">
        <template slot-scope="{row}">
          <span>{{ row.subject }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Message" min-width="150px">
        <template slot-scope="{row}">
          <span >{{ row.message }}</span>
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
    <!-- <el-dialog title="Inquiry Details" width="80%" top="2vh" :visible.sync="dialogInquiryVisible">
      <el-tabs type="border-card">
        <el-tab-pane label="Details">
          <el-form ref="dataForm" :rules="rules" :model="temp" label-position="left" label-width="150px" style=" margin-left:50px;">
            <el-row :gutter="20">
              <el-col :span="12">
                <el-form-item label="Name" prop="name">
                  <el-input v-model="temp.name" />
                </el-form-item>

                <el-form-item label="Description" prop="description">
                  <el-input
                    type="textarea"
                    v-model="temp.description"
                    :rows="2"
                    placeholder="Please Enter description">
                  </el-input>
                </el-form-item>
              </el-col>
              <el-col :span="12">

              </el-col>
            </el-row>
          </el-form>
        </el-tab-pane>
        
      </el-tabs>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogInquiryVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" @click="dialogStatus==='create'?createData():updateData()">
          Save
        </el-button>
      </div>
    </el-dialog> -->
  </div>
</template>

<script>
import {
  fetchList,
  changeInquiryStatus,
  deleteInquiry,
} from "@/api/admin/inquiries";
import waves from "@/directive/waves"; 
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 
import axios from "axios";

export default {
  name: "ComplexTable",
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
        limit: 5,
        name: undefined,
        is_attended:'all',
        sort: "+id"
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        id:undefined,
        name: undefined,
        description: undefined,
      },
      dialogInquiryVisible: false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        name: [
          { required: true, message: "Name is required", trigger: "blur" }
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

    handleModifyStatus(row, status) {
      let data={'id':row.id,'is_attended':status};
      changeInquiryStatus(data).then((response) => {
        this.$notify({
          title: "Success",
          message: "Status changed Successfully",
          type: "success",
          duration: 2000
        })
      })

      row.is_attended = status;
    },

    sortByID(order) {
      if (order === "ascending") {
        this.listQuery.sort = "+id";
      } else {
        this.listQuery.sort = "-id";
      }
      this.handleFilter();
    },
   
    
    handleDelete(row) {
      deleteInquiry(row.id).then((data) => {
        this.$notify({
            title: "Success",
            message: "Delete Successfully",
            type: "success",
            duration: 2000
        });
        const index = this.list.indexOf(row);
        this.list.splice(index, 1);
      });
    },
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "ID",
          "Name",
          "Email",
          "Contact",
          "Message",
          "Created at"
        ];
        const filterVal = [
          "id",
          "name",
          "email",
          "contact",
          "message",
          "created_at"
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "inquiries"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "timestamp") {
            return parseTime(v[j]);
          } else {
            return v[j];
          }
        })
      );
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
.edit-input {
  padding-right: 100px;
}
.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}
</style>
