<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
  <script src="{{ asset('vendor/vue/2.6.12/vue.js') }}"></script>
  <script src="{{ asset('vendor/element-ui/2.6.2/js.js') }}"></script>
  <script src="{{ asset('vendor/jquery/jquery-3.6.0.min.js') }}"></script>
  <script src="{{ asset('vendor/layer/3.5.1/layer.js') }}"></script>
  <link href="{{ mix('/build/beike/admin/css/bootstrap.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('vendor/element-ui/2.6.2/css.css') }}">
  <link href="{{ mix('build/beike/admin/css/filemanager.css') }}" rel="stylesheet">
  <script src="{{ mix('build/beike/admin/js/app.js') }}"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>beike filemanager</title>
</head>
<body class="page-filemanager">
  <div class="filemanager-wrap" id="filemanager-wrap-app" v-cloak ref="splitPane">
    <div class="filemanager-navbar" :style="'width:' + paneLengthValue">
      <el-tree
        :props="defaultProps"
        node-key="path"
        :load="loadNode"
        lazy
        :default-expanded-keys="['/catalog']"
        :render-after-expand="false"
        highlight-current
        ref="tree"
        @node-click="handleNodeClick"
        class="tree-wrap">
        <div class="custom-tree-node" slot-scope="{ node, data }">
          <div>@{{ node.label }}</div>
          <div class="right" v-if="node.isCurrent">
            <el-tooltip class="item" effect="dark" content="创建文件夹" placement="top">
              <span @click.stop="() => {openInputBox('addFolder', data)}"><i class="el-icon-circle-plus-outline"></i></span>
            </el-tooltip>

            <el-tooltip class="item" effect="dark" content="重命名" placement="top">
              <span v-if="node.level != 1" @click.stop="() => {openInputBox('folder', data)}"><i class="el-icon-edit"></i></span>
            </el-tooltip>

            <el-tooltip class="item" effect="dark" content="删除" placement="top">
              <span v-if="node.level != 1" @click.stop="() => {deleteFolder(node, data)}"><i class="el-icon-delete"></i></span>
            </el-tooltip>

          </div>
        </div>
      </el-tree>
    </div>
    <div class="filemanager-divider" @mousedown="handleMouseDown"></div>
    <div class="filemanager-content" v-loading="loading" element-loading-background="rgba(255, 255, 255, 0.5)">
      <div class="content-head">
        <div class="left">
          <el-link :underline="false" :disabled="editingImageIndex === null" icon="el-icon-download">下载</el-link>
          <el-link :underline="false" :disabled="editingImageIndex === null" @click="deleteFile" icon="el-icon-delete">删除</el-link>
          <el-link :underline="false" :disabled="editingImageIndex === null" @click="openInputBox('image')" icon="el-icon-edit">重命名</el-link>
        </div>
        <div class="right"><el-button size="mini" type="primary">上传文件</el-button></div>
      </div>
      <div class="content-center">
        <div :class="['image-list', file.selected ? 'active' : '']" v-for="file, index in images" :key="index" @click="checkedImage(index)">
          <div class="img"><img :src="file.url"></div>
          <div class="text">
            <span :title="file.name">@{{ file.name }}</span>
            <i v-if="file.selected" class="el-icon-check"></i>
          </div>
        </div>
      </div>
      <div class="content-footer">
        <div class="right"></div>
        <div class="pagination-wrap">
          <el-pagination
            @current-change="pageCurrentChange"
            :page-size="20"
            layout="prev, pager, next"
            :total="image_total">
          </el-pagination>
        </div>
        <div class="right"><el-button size="mini" type="primary" @click="fileChecked" :disabled="editingImageIndex === null">选择</el-button></div>
      </div>
    </div>
  </div>

  <script>
  var callback = null;

  var app = new Vue({
    el: '#filemanager-wrap-app',
    components: {},
    data: {
      min: 10,
      max: 40,
      paneLengthPercent: 20,
      triggerLength: 10,

      loading: false,

      editingImageIndex: null,

      treeInit: [
        {
          name: '图片空间',
          path: '/catalog',
          selected: true,
          children: [
          {
            name: '图片空间',
            path: '/catalog',
            selected: true,
            children: [
            ]
          },
          ]
        },
      ],

      defaultProps: {
        children: 'children',
        label: 'name'
      },

      folderCurrent: '/catalog',

      triggerLeftOffset: 0,

      images: @json($images),
      image_total: 0,
      image_page: 1,
    },
    // 计算属性
    computed: {
      // isFileSelected() {
      //   return this.images.some(file => file.selected);
      // },

      paneLengthValue() {
        return `calc(${this.paneLengthPercent}% - ${this.triggerLength / 2 + 'px'})`
      },
    },
    // 侦听器
    watch: {},
    // 组件方法
    methods: {
      handleNodeClick(e) {
        if (e.path == this.folderCurrent) {
          return;
        }

        this.folderCurrent = e.path
        this.loadData()
      },

      pageCurrentChange(e) {
        this.image_page = e
        this.loadData()
      },

      loadData() {
        $http.get(`/panel/file_manager?base_folder=${this.folderCurrent}`, {page: this.image_page}).then((res) => {
          this.images = res.images
          this.image_page = res.image_page
          this.image_total = res.image_total
        })
      },

      loadNode(node, resolve) {
        let treeInit = [{name: '图片空间', path: '/catalog', selected: true, children: []}]
        if (node.level === 0) {
          return resolve(treeInit);
        }

        if (node.level === 1) return resolve(@json($folders));

        $http.get(`/panel/file_manager?base_folder=${node.data.path}`).then((res) => {
          resolve(res.folders);
        })
      },

      // 按下滑动器
      handleMouseDown(e) {
        document.addEventListener('mousemove', this.handleMouseMove)
        document.addEventListener('mouseup', this.handleMouseUp)

        this.triggerLeftOffset = e.pageX - e.srcElement.getBoundingClientRect().left
      },

      // 按下滑动器后移动鼠标
      handleMouseMove(e) {
        const clientRect = this.$refs.splitPane.getBoundingClientRect()
        let paneLengthPercent = 0

        const offset = e.pageX - clientRect.left - this.triggerLeftOffset + this.triggerLength / 2
        paneLengthPercent = (offset / clientRect.width) * 100

        if (paneLengthPercent < this.min) {
          paneLengthPercent = this.min
        }
        if (paneLengthPercent > this.max) {
          paneLengthPercent = this.max
        }
        this.paneLengthPercent = paneLengthPercent;
      },

      // 松开滑动器
      handleMouseUp() {
        document.removeEventListener('mousemove', this.handleMouseMove)
      },

      checkedImage(index) {
        this.editingImageIndex = index;
        this.images.map(e => !e.index ? e.selected = false : '')
        this.images[index].selected = !this.images[index].selected
      },

      fileChecked() {
        let typedFiles = this.images[this.editingImageIndex];

        if (callback !== null) {
          callback(typedFiles);
        }

        // 关闭弹窗
        var index = parent.layer.getFrameIndex(window.name);
        parent.layer.close(index);
      },

      deleteFile() {
        this.$confirm('是否要删除选中文件', '提示', {
          type: 'warning'
        }).then(() => {
          this.images.splice(this.editingImageIndex, 1);
          this.$message({type: 'success',message: '删除成功!'});
        }).catch(_=>{});
      },

      deleteFolder(node, data) {
        console.log(node, data)
        // console.log(node.parent.data.id)
        if (node.parent.data.key) {
          this.$nextTick(() => {
            this.$refs.tree.setCurrentKey(node.parent.data.key)
          })
        }
      },

      openInputBox(type, data) {
        // console.log(data)
        // console.log(this.editingImageIndex)
        this.$prompt('', type=='addFolder' ? '新建文件夹' : '重命名', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          inputPattern: /^.+$/,
          inputErrorMessage: '不能为空'
        }).then(({ value }) => {
          this.$message({
            type: 'success',
            message: '你的邮箱是: ' + value
          });
        }).catch(() => {});
      }
    },
    // 在实例初始化之后，组件属性计算之前，如data属性等
    beforeCreate () {
    },
    // 在实例创建完成后被立即同步调用
    created () {
    },
    // 在挂载开始之前被调用:相关的 render 函数首次被调用
    beforeMount () {
    },
    // 实例被挂载后调用
    mounted () {
    },
  })

  $(document).ready(function() {
    $(document).on('click', function (e) {
      if ($(e.target).closest('.content-center .image-list, .content-head, .content-footer').length === 0) {
        app.editingImageIndex = null;
        app.images.map(e => e.selected = false)
      }
    })
  });
  </script>
</body>
</html>
