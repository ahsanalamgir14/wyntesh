<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="listQuery.search" placeholder="Search Records" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">Search</el-button>
      <el-button class="filter-item" style="margin-left: 10px;" type="success" @click="handleCreate"><i class="fas fa-plus"></i> Create New Contest</el-button>
    </div>
    <el-table :key="tableKey" v-loading="listLoading" :data="list" border fit highlight-current-row style="width: 100%;" @sort-change="sortChange">
      <el-table-column label="ID" prop="id" sortable="custom" align="center" width="80" :class-name="getSortClass('id')">
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Actions" align="center" width="150px" class-name="small-padding">
        <template slot-scope="{row}">
          <el-button type="success" :loading="buttonLoading" icon="el-icon-check" circle @click="handleStartContest(row)"></el-button>
          <el-button type="primary" :loading="buttonLoading" icon="el-icon-edit" circle @click="handleEdit(row)"></el-button>
          <el-button icon="el-icon-delete" circle type="danger" @click="deleteData(row)"></el-button>
        </template>
      </el-table-column>
      <el-table-column label="Name" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Running ?" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.is_current?'Yes':'No' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Start date" width="150px" align="">
        <template slot-scope="{row}">
          <span>{{ row.start_date | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>
      <el-table-column label="End date" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.end_date | parseTime('{y}-{m}-{d}')}}</span>
        </template>
      </el-table-column>
      <el-table-column label="Image" min-width="150px">
        <template slot-scope="{row}">
          <a :href="row.image" class="link-type" type="primary" target="_blank">View Image</a>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />
    <el-dialog :title="textMap[dialogStatus]" width="60%" top="30px" :visible.sync="dialogContestVisible">
      <el-form ref="dataForm" :rules="rules" :model="temp">
        <el-row>
          <el-col :xs="24" :sm="12" :md="16" :lg="16" :xl="16">
            <el-form-item label="Name" prop="name">
              <el-input v-model="temp.name" />
            </el-form-item>
            <el-form-item label="Description" prop="description">
              <el-input type="textarea" :rows="4" placeholder="Description" v-model="temp.description">
              </el-input>
            </el-form-item>
            <el-form-item label="Number of Winners" prop="number_of_winners">
              <el-input type="number" v-model="temp.number_of_winners" />
            </el-form-item>
            <el-form-item label="Start Date" prop="start_date">
              </br>
              <el-date-picker v-model="temp.start_date" type="date" format="yyyy-MM-dd" value-format="yyyy-MM-dd" placeholder="Start Date">
              </el-date-picker>
            </el-form-item>
            <el-form-item label="End Date" prop="end_date">
              </br>
              <el-date-picker v-model="temp.end_date" type="date" format="yyyy-MM-dd" value-format="yyyy-MM-dd" placeholder="End Date">
              </el-date-picker>
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="12" :md="16" :lg="8" :xl="8">
            <div class="img-upload">
              <el-form-item prop="image">
                <label for="Image">Image</label>
                <el-upload class="avatar-uploader" action="#" ref="upload" :show-file-list="true" :auto-upload="false" :on-change="handleChange" :on-remove="handleRemove" :limit="3" :file-list="fileList" :on-exceed="handleExceed" accept="image/png, image/jpeg">
                  <img v-if="temp.image" :src="temp.image" class="avatar">
                  <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                </el-upload>
                <p>Click to upload image.</p>
              </el-form-item>
            </div>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogContestVisible = false">
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
  getContests,
  deleteContest,
  createContest,
  updateContest,
  startContest
} from "@/api/admin/contests";
import waves from "@/directive/waves";
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";
import Tinymce from '@/components/Tinymce'

export default {
  name: "contests",
  components: { Pagination, Tinymce },
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
        search: undefined,
        sort: "+id"
      },
      fileList: [],
      file: undefined,
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        name: undefined,
        description: undefined,
        start_date: undefined,
        end_date: undefined,
        number_of_winners: 1,
        image: undefined
      },

      dialogContestVisible: false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        name: [{ required: true, message: 'name is required', trigger: 'blur' }],
        start_date: [{ required: true, message: 'Start date is required', trigger: 'blur' }],
        end_date: [{ required: true, message: 'End date is required', trigger: 'blur' }],
        number_of_winners: [{ required: true, message: 'Number of winner is required', trigger: 'blur' }]
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
  },
  methods: {
    handleChange(f, fl) {
      if (fl.length > 1) {
        fl.shift()
      }
      this.file = f.raw
    },
    handleRemove(file, fileList) {
      this.file = undefined;
      this.fileList = [];
    },
    handleExceed(files, fileList) {
      this.$message.warning(`You can not select more than one file, please remove first.`);
    },
    getList() {
      this.listLoading = true;
      getContests(this.listQuery).then(response => {
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
    sortByID(order) {
      if (order === "ascending") {
        this.listQuery.sort = "+id";
      } else {
        this.listQuery.sort = "-id";
      }
      this.handleFilter();
    },
    resetTemp() {
      this.temp = {
        name: undefined,
        description: undefined,
        start_date: undefined,
        end_date: undefined,
        number_of_winners: 1,
        image: undefined
      };
      this.file = undefined
      this.fileList = [];
    },
    handleCreate() {
      this.fileList = [];
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogContestVisible = true;
      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });
    },
    createData() {

      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          this.buttonLoading = true;
          var form = new FormData();
          let form_data = this.temp;

          for (var key in form_data) {
            if (form_data[key] !== undefined && form_data[key] !== null) {
              form.append(key, form_data[key]);
            }
          }

          form.append('image', this.file);

          createContest(form).then((data) => {
            this.getList();
            this.dialogContestVisible = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading = false;
            this.resetTemp();
          }).catch((err) => {
            this.buttonLoading = false;
          });
        }
      });
    },
    handleEdit(row) {
      this.fileList = [];
      this.file = undefined;
      this.temp = Object.assign({}, row); // copy obj
      if (row.is_visible == 1) {
        this.temp.is_visible = true
      } else {
        this.temp.is_visible = false
      }
      this.dialogStatus = "update";
      this.dialogContestVisible = true;
      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });
    },
    updateData() {

      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          this.buttonLoading = true;
          var form = new FormData();
          const tempData = Object.assign({}, this.temp);

          for (var key in tempData) {
            if (tempData[key] !== undefined && tempData[key] !== null) {
              form.append(key, tempData[key]);
            }
          }

          form.append('image', this.file);


          updateContest(form).then((data) => {
            this.getList();
            this.dialogContestVisible = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading = false;
            this.resetTemp();
          }).catch((err) => {
            this.buttonLoading = false;
          });
        }
      });

    },
    handleStartContest(row) {

      this.$confirm('Are you sure you want Start Contest?', 'Warning', {
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        this.buttonLoading = true;

        startContest(row.id).then((data) => {
          this.dialogContestVisible = false;
          this.buttonLoading = false;
          this.$notify({
            title: "Success",
            message: data.message,
            type: "success",
            duration: 2000
          });
          this.getList();
        }).catch((err) => {
          this.buttonLoading = false;
        });

      })

      
    },
    deleteData(row) {
      deleteContest(row.id).then((data) => {
        this.dialogContestVisible = false;
        this.$notify({
          title: "Success",
          message: data.message,
          type: "success",
          duration: 2000
        });
        this.getList();
      });
    },
    getSortClass: function(key) {
      const sort = this.listQuery.sort;
      return sort === `+${key}` ?
        "ascending" :
        sort === `-${key}` ?
        "descending" :
        "";
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
  .img-upload {
    float: right;
    margin-right: 20px;
  }
}

</style>
