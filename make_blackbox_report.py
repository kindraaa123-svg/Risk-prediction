from docx import Document
from docx.enum.section import WD_ORIENT
from docx.enum.table import WD_CELL_VERTICAL_ALIGNMENT, WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Inches, Pt, RGBColor


OUTPUT = 'blackbox.docx'


def set_cell_shading(cell, fill):
    properties = cell._tc.get_or_add_tcPr()
    shading = properties.find(qn('w:shd'))
    if shading is None:
        shading = OxmlElement('w:shd')
        properties.append(shading)
    shading.set(qn('w:fill'), fill)


def set_cell_borders(cell, color='D9D9D9', size='6'):
    properties = cell._tc.get_or_add_tcPr()
    borders = properties.first_child_found_in('w:tcBorders')
    if borders is None:
        borders = OxmlElement('w:tcBorders')
        properties.append(borders)
    for edge in ('top', 'left', 'bottom', 'right', 'insideH', 'insideV'):
        tag = 'w:' + edge
        element = borders.find(qn(tag))
        if element is None:
            element = OxmlElement(tag)
            borders.append(element)
        element.set(qn('w:val'), 'single')
        element.set(qn('w:sz'), size)
        element.set(qn('w:space'), '0')
        element.set(qn('w:color'), color)


def set_cell_margins(cell, top=90, start=100, bottom=90, end=100):
    properties = cell._tc.get_or_add_tcPr()
    margins = properties.first_child_found_in('w:tcMar')
    if margins is None:
        margins = OxmlElement('w:tcMar')
        properties.append(margins)
    for name, value in [('top', top), ('start', start), ('bottom', bottom), ('end', end)]:
        node = margins.find(qn('w:' + name))
        if node is None:
            node = OxmlElement('w:' + name)
            margins.append(node)
        node.set(qn('w:w'), str(value))
        node.set(qn('w:type'), 'dxa')


def set_repeat_table_header(row):
    properties = row._tr.get_or_add_trPr()
    repeat = OxmlElement('w:tblHeader')
    repeat.set(qn('w:val'), 'true')
    properties.append(repeat)


def set_width(cell, width):
    cell.width = Inches(width)
    properties = cell._tc.get_or_add_tcPr()
    width_node = properties.find(qn('w:tcW'))
    if width_node is None:
        width_node = OxmlElement('w:tcW')
        properties.append(width_node)
    width_node.set(qn('w:w'), str(int(width * 1440)))
    width_node.set(qn('w:type'), 'dxa')


def add_text(cell, text, bold=False, color='1F2937', size=8.2, align=WD_ALIGN_PARAGRAPH.LEFT):
    cell.text = ''
    paragraph = cell.paragraphs[0]
    paragraph.alignment = align
    paragraph.paragraph_format.space_after = Pt(0)
    paragraph.paragraph_format.line_spacing = 1.0
    run = paragraph.add_run(text)
    run.bold = bold
    run.font.name = 'Aptos'
    run.font.size = Pt(size)
    run.font.color.rgb = RGBColor.from_string(color)


def add_body_paragraph(document, text, bold_lead=None):
    paragraph = document.add_paragraph()
    paragraph.paragraph_format.space_after = Pt(6)
    paragraph.paragraph_format.line_spacing = 1.08
    if bold_lead and text.startswith(bold_lead):
        lead = paragraph.add_run(bold_lead)
        lead.bold = True
        lead.font.color.rgb = RGBColor(31, 41, 55)
        rest = paragraph.add_run(text[len(bold_lead):])
        rest.font.color.rgb = RGBColor(55, 65, 81)
    else:
        run = paragraph.add_run(text)
        run.font.color.rgb = RGBColor(55, 65, 81)
    for run in paragraph.runs:
        run.font.name = 'Aptos'
        run.font.size = Pt(9.5)
    return paragraph


rows = [
    ('1', 'BB-01', 'Akses halaman awal sebagai pengguna belum login', 'Buka /', 'Dialihkan ke halaman masuk karena / mengarah ke /dashboard yang dilindungi auth.', 'Belum dijalankan; vendor/autoload.php tidak tersedia.', 'TERTAHAN'),
    ('2', 'BB-02', 'Login dengan kredensial valid', 'admin@example.com / change-me-now', 'Login berhasil dan diarahkan ke dashboard.', 'Akun default teridentifikasi di seeder; submit UI belum dapat diuji.', 'TERTAHAN'),
    ('3', 'BB-03', 'Login dengan kredensial salah', 'Email benar, kata sandi salah', 'Tetap di halaman login dan menampilkan pesan validasi/gagal login.', 'Form dan error state tersedia di UI; respons runtime belum teramati.', 'TERTAHAN'),
    ('4', 'BB-04', 'Membuka dashboard setelah login', 'Sesi pengguna aktif', 'Ringkasan pengguna, proyek, proyek berisiko tinggi, grafik status, dan aktivitas tampil.', 'Komponen dashboard teridentifikasi; render browser belum dapat diuji.', 'TERTAHAN'),
    ('5', 'BB-05', 'Melihat dan mencari daftar proyek', 'Buka /projects, isi kata kunci', 'Daftar proyek tampil dan hasil menyempit sesuai pencarian.', 'Halaman memiliki pencarian dan tabel proyek; runtime belum teramati.', 'TERTAHAN'),
    ('6', 'BB-06', 'Validasi tambah proyek', 'Kosongkan nama/kode atau pilih tanggal tidak valid', 'Form menolak data dan menampilkan error pada field terkait.', 'ProjectRequest memiliki required, unique, in-list, dan date validation; belum submit UI.', 'TERTAHAN'),
    ('7', 'BB-07', 'Menyimpan proyek baru', 'Data proyek lengkap dan valid', 'Proyek tersimpan, pengguna kembali ke daftar/detail, dan notifikasi sukses tampil.', 'Route POST dan form tersedia; perubahan database belum dapat diverifikasi.', 'TERTAHAN'),
    ('8', 'BB-08', 'Melihat detail, mengubah, dan menghapus proyek', 'Pilih proyek lalu gunakan aksi detail/edit/hapus', 'Detail tampil; edit menyimpan perubahan; hapus meminta konfirmasi lalu menghapus data.', 'Route dan modal konfirmasi teridentifikasi; aksi destruktif belum dijalankan.', 'TERTAHAN'),
    ('9', 'BB-09', 'Membuat task dengan validasi proyek/status/prioritas', 'Data task valid dan tidak valid', 'Data invalid ditolak; data valid tersimpan dan muncul di daftar task.', 'TaskRequest memiliki required, exists, in-list, dan max validation; runtime belum teramati.', 'TERTAHAN'),
    ('10', 'BB-10', 'Membuka modul risk prediction dan risk mitigation', 'Buka /risk-prediction dan /risk-mitigation', 'Halaman modul tampil sesuai permission pengguna.', 'Route dan permission middleware terdefinisi; render browser belum dapat diuji.', 'TERTAHAN'),
    ('11', 'BB-11', 'Membuka laporan dan filter aktivitas admin', 'Buka /reports dan /admin/activity-logs; gunakan filter', 'Laporan/aktivitas tampil; filter dan reset menghasilkan data yang sesuai.', 'Route serta kontrol filter teridentifikasi dari frontend; runtime belum teramati.', 'TERTAHAN'),
    ('12', 'BB-12', 'Memperbarui profil dan mengganti kata sandi', 'Nama/email/telepon; kata sandi lama, baru, konfirmasi', 'Profil valid tersimpan; kata sandi mengikuti min 8 karakter dan konfirmasi.', 'Request validation teridentifikasi; update UI belum dapat dijalankan.', 'TERTAHAN'),
    ('13', 'BB-13', 'Mengakses menu admin dengan hak Superadmin', 'Buka users, access-control, settings, backups', 'Menu dan halaman admin tersedia untuk role yang memiliki permission.', 'Permission middleware dan menu berbasis can() teridentifikasi; runtime belum teramati.', 'TERTAHAN'),
    ('14', 'BB-14', 'Menguji pembatasan akses role non-admin', 'Akun tanpa users.view/roles.manage', 'Akses ditolak atau menu admin tidak ditampilkan.', 'Middleware permission terdefinisi pada route admin; belum diuji dengan sesi role.', 'TERTAHAN'),
    ('15', 'BB-15', 'Build frontend produksi', 'npm run build', 'Bundle berhasil dibuat tanpa error kompilasi.', 'Berhasil; 3.030 modul ditransformasi. Ada warning opsional fontaine dan chunk >500 kB.', 'LULUS'),
]


document = Document()
section = document.sections[0]
section.orientation = WD_ORIENT.PORTRAIT
section.page_width = Inches(8.27)
section.page_height = Inches(11.69)
section.top_margin = Inches(0.65)
section.bottom_margin = Inches(0.65)
section.left_margin = Inches(0.7)
section.right_margin = Inches(0.7)

styles = document.styles
styles['Normal'].font.name = 'Aptos'
styles['Normal'].font.size = Pt(9.5)
styles['Normal'].font.color.rgb = RGBColor(55, 65, 81)

title = document.add_paragraph(style='Title')
title.alignment = WD_ALIGN_PARAGRAPH.LEFT
title.paragraph_format.space_after = Pt(4)
title_run = title.add_run('Laporan Black Box Testing')
title_run.font.name = 'Aptos Display'
title_run.font.size = Pt(22)
title_run.font.bold = True
title_run.font.color.rgb = RGBColor(0, 0, 0)

subtitle = document.add_paragraph()
subtitle.paragraph_format.space_after = Pt(14)
run = subtitle.add_run('Sistem Informasi Manajemen Proyek Berbasis Risk Prediction')
run.font.name = 'Aptos'
run.font.size = Pt(11)
run.font.color.rgb = RGBColor(37, 99, 235)
run.bold = True

add_body_paragraph(document, 'Laporan ini merangkum pengujian black-box terhadap alur utama aplikasi berdasarkan route, komponen antarmuka, aturan validasi, dan hasil verifikasi build frontend. Pengujian runtime melalui browser dan test suite PHP belum dapat diselesaikan karena dependensi Composer belum tersedia setelah proses instalasi dihentikan.')

heading = document.add_paragraph()
heading.paragraph_format.space_before = Pt(8)
heading.paragraph_format.space_after = Pt(5)
run = heading.add_run('Ringkasan hasil')
run.bold = True
run.font.name = 'Aptos Display'
run.font.size = Pt(13)
run.font.color.rgb = RGBColor(0, 0, 0)

summary = document.add_table(rows=1, cols=3)
summary.alignment = WD_TABLE_ALIGNMENT.LEFT
summary.autofit = False
for cell, width, text in zip(summary.rows[0].cells, [1.95, 1.95, 1.95], ['Total skenario', 'Lulus terverifikasi', 'Tertahan lingkungan']):
    set_width(cell, width)
    set_cell_shading(cell, 'EFF6FF')
    set_cell_borders(cell)
    set_cell_margins(cell)
    add_text(cell, text, bold=True, color='1D4ED8', size=8.5, align=WD_ALIGN_PARAGRAPH.CENTER)
row = summary.add_row()
for cell, width, text in zip(row.cells, [1.95, 1.95, 1.95], ['15', '1', '14']):
    set_width(cell, width)
    set_cell_borders(cell)
    set_cell_margins(cell, top=120, bottom=120)
    add_text(cell, text, bold=True, color='111827', size=15, align=WD_ALIGN_PARAGRAPH.CENTER)

add_body_paragraph(document, 'Kesimpulan: frontend berhasil dibangun. Verifikasi fungsional end-to-end masih diperlukan setelah dependensi Composer tersedia dan aplikasi dapat dijalankan pada browser.')

heading = document.add_paragraph()
heading.paragraph_format.space_before = Pt(8)
heading.paragraph_format.space_after = Pt(5)
run = heading.add_run('Tabel hasil pengujian')
run.bold = True
run.font.name = 'Aptos Display'
run.font.size = Pt(13)
run.font.color.rgb = RGBColor(0, 0, 0)

table = document.add_table(rows=1, cols=6)
table.alignment = WD_TABLE_ALIGNMENT.CENTER
table.autofit = False
table.style = 'Table Grid'
headers = ['No', 'ID', 'Skenario uji', 'Hasil yang diharapkan', 'Hasil aktual dan bukti', 'Status']
widths = [0.35, 0.55, 1.25, 1.55, 1.95, 0.55]
for cell, width, text in zip(table.rows[0].cells, widths, headers):
    set_width(cell, width)
    set_cell_shading(cell, '1D4ED8')
    set_cell_borders(cell, color='D9D9D9')
    set_cell_margins(cell, top=100, bottom=100)
    add_text(cell, text, bold=True, color='FFFFFF', size=7.8, align=WD_ALIGN_PARAGRAPH.CENTER)
set_repeat_table_header(table.rows[0])

for index, row_data in enumerate(rows):
    row = table.add_row()
    for cell, width, text in zip(row.cells, widths, row_data):
        set_width(cell, width)
        set_cell_borders(cell)
        set_cell_margins(cell)
        cell.vertical_alignment = WD_CELL_VERTICAL_ALIGNMENT.CENTER
        if index % 2 == 1:
            set_cell_shading(cell, 'F8FAFC')
        align = WD_ALIGN_PARAGRAPH.CENTER if text in ('TERTAHAN', 'LULUS') or cell is row.cells[0] or cell is row.cells[1] else WD_ALIGN_PARAGRAPH.LEFT
        color = 'B45309' if text == 'TERTAHAN' else ('047857' if text == 'LULUS' else '1F2937')
        add_text(cell, text, bold=text in ('TERTAHAN', 'LULUS'), color=color, size=7.6, align=align)

for paragraph in document.paragraphs:
    paragraph.paragraph_format.keep_together = False

document.add_paragraph()
note = document.add_paragraph()
note.paragraph_format.space_before = Pt(5)
note.paragraph_format.space_after = Pt(0)
run = note.add_run('Catatan pengujian: ')
run.bold = True
run.font.name = 'Aptos'
run.font.size = Pt(8.5)
run.font.color.rgb = RGBColor(31, 41, 55)
run = note.add_run('status TERTAHAN berarti skenario sudah didefinisikan dan artefak terkait ditemukan, tetapi belum boleh disebut lulus/gagal karena belum dieksekusi di runtime aplikasi.')
run.font.name = 'Aptos'
run.font.size = Pt(8.5)
run.font.color.rgb = RGBColor(75, 85, 99)

document.save(OUTPUT)
print(OUTPUT)
