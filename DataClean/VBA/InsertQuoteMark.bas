' Author: Veck Hsiao 2014/07/18 @ PLSM, CS, NCCU, Taiwan
' Usage: 將每格加上前後雙引號

Sub InsertQuoteMark()
    Dim i As Integer
    Dim j As Integer
    Dim RowMax As Integer
    Dim ColumnMax As Integer
    Dim table As String
    
    ColumnMax = [number of column]    '縱軸
    RowMax = [numbers of row]         '橫軸
    
    sheet = "[sheet name]"
    
    For i = 1 To ColumnMax      'Row-based is faser than column-based
        For j = 1 To RowMax
            Worksheets(sheet).Cells(i, j) = Chr(34) & Worksheets(sheet).Cells(i, j) & Chr(34)   'Chr(34) is VB Char Code of " (double quote mark' 
        Next j
    Next i        
End Sub

'加完後用 excel 開會只有 "A"，但用 notepad 開會是 """A"""，所以用 VBA 轉完要在用 notepad 檢查，可以用 Replace All 一次取代 """ 為 "，替換完在用 excel 會看不見 "，沒關係
'時間 yyyy-mm-dd hh:mm:ss 在 excel 中雖然可以用修改格式為自訂，但是一旦加上 "" 就會變成字串而亂掉，解決方法是先改成要求的格式後，在 notepad 中使用取代，將 『,2013』 取代為 『,"2013』，將『\n』取代為『"\n』(這個功能在Sublime Text 2 中要把 Replace 的 Regular Expression 勾起來)