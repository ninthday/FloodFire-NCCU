' Author: Veck Hsiao 2014/07/18 @ PLSM, CS, NCCU, Taiwan
' Usage: 處理 Excel 匯出成 csv 或 txt 等文字檔時，純文字因含有換行符號而斷行的問題 => 一律將換行符號取代為 '，'

Sub Newline()
    Dim src As String
    Dim i As Integer
    Dim column As Integer

    src = "[table_tobe_processed]"
    column = [column_of_text_tobe_replaced]

    For i = 2 To [final row]
        If Not InStr(Worksheets(src).Cells(i, column), vbCr) = 0 Then
            Worksheets(src).Cells(i, column) = Replace(Worksheets(src).Cells(i, column), vbCr, "，")
        End If
    Next i
End Sub


