' Author: Veck Hsiao 2014/02 @ PLSM, CS, NCCU, Taiwan
' Usage: Translate timestamp from UTC +0000 to +0800 (Taipei Time Zone) of Twitter date set

Sub ToTaiwan()
    Dim parse() As String
    Dim i As Integer
    Dim sheet As String
    Dim date_column As Integer

    sheet = "[sheet_tobe_stranslate]"
    date_column = [column_of_date]
    
    For i = 2 To [final_row]
    
        parse = Split(Sheets(sheet).Cells(i, date_column))
        
        '組合成 YYYY/MM/DD  hh:mm:ss PM/AM 格式，以便累加小時後自動跨日
        '[ parse(5)/parse(1)/parse(2) parse(3) ]
        
        Dim formatted As String
        Dim month As String
        
        'You could customize your month cases
        Select Case parse(1)    'switch case
            Case "Jul"
                month = "7"
            Case "Aug"
                month = "8"
            Case "Sep"
                month = "9"
        End Select
        
        formatted = parse(5) + "/" + month + "/" + parse(2) + " " + parse(3)
        'Debug.Print formatted
        
        Dim TaiwanTime As Date
        TaiwanTime = formatted
        'Debug.Print DateAdd("h", "8", TaiwanTime)
        
        Sheets(sheet).Cells(i, date_column) = DateAdd("h", "8", formatted)    'Taiwan is +08:00
        
     Next i
End Sub
