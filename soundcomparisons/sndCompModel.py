# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from sqlalchemy import String
from sqlalchemy.dialects.mysql import TINYINT, TIMESTAMP, TEXT, BIGINT, INTEGER, FLOAT


class SndCompModel():
    '''
        The general model to become a parant for the database models defined in db.py
    '''
    def toDict(self):
        '''
            @return dict {}
            Serialize a Model to a dict that maps its column names to column values.
        '''
        return {c.name: getattr(self, c.name) for c in self.__table__.columns}

    def validateString(self, entry):
        '''
            @return (entry', [String])
            Helper for validate method.
            Returns a tuple of a possibly modified entry and a list of errors.
        '''
        if isinstance(entry, str):
            return entry, []
        return entry, ["Entry '%s' is not a string." % entry]

    def validateInt(self, entry):
        '''
            @return (entry', [String])
            Helper for validate method.
            Returns a tuple of a possibly modified entry and a list of errors.
        '''
        if isinstance(entry, int):
            return entry, []
        try:
            i = int(entry)
            return i, []
        except:
            return entry, ["Could not convert entry '%s' to int." % entry]

    def validateFloat(self, entry):
        '''
            @return (entry', [String])
            Helper for validate method.
            Returns a tuple of a possibly modified entry and a list of errors.
        '''
        if isinstance(entry, float):
            return entry, []
        try:
            f = float(entry)
            return f, []
        except:
            return entry, ["Could not convert entr '%s' to float." % entry]

    def validate(self):
        '''
            @return (errors [String], warnings [String])
            Performs a simple form of validation on the SndCompModel.
            To do this it basically iterates all columns,
            and tests if the according field exists.
            If the field exists it is further tested that the fields type looks ok.
            In case of a problem the returned list of errors will not be empty.
            Returning a list instead of throwing an exception
            allows us to report multiple errors at once
            thus making imports a little bit more friendly.
        '''
        errors, warnings = [], []
        for c in self.__table__.columns:
            if not hasattr(self, c.name):
                if c.nullable:
                    # A missing but nullable column is only a warning and will get added.
                    warnings.append("Adding missing column '%s' to model." % c.name)
                    setattr(self, c.name, None)
                else:
                    # A missing non nullable column is an error.
                    errors.append("Model is missing column '%s'." % c.name)
            else:
                # Checking None case for entry:
                entry = getattr(self, c.name)
                if entry == None:
                    if c.nullable:
                        continue
                    if c.default != None:
                        setattr(self, c.name, c.default)
                        warnings.append(
                            "Correcting entry '%s' for column '%s' to '%s'."
                            % (entry, c.name, c.default))
                        continue
                # A column exists, and we can check its type.
                types = [
                    ('STRING', String),
                    ('STRING', TEXT),
                    ('INT', TINYINT),
                    ('INT', TIMESTAMP),
                    ('INT', BIGINT),
                    ('INT', INTEGER),
                    ('FLOAT', FLOAT)]
                # Searching label…
                for (l, t) in types:
                    if isinstance(c.type, t):
                        label = l
                        break
                else:
                    errors.append("Unknown type: '%s'" % c.type)
                    continue
                # Validation depending on label:
                choice = {  # String -> (entry -> (entry, [String]))
                    'STRING': self.validateString,
                    'INT': self.validateInt,
                    'FLOAT': self.validateFloat}
                if label not in choice:
                    errors.append("label not in choice: '%s'" % label)
                    continue
                # Call to validation helpers:
                entry, err = choice[label](entry)
                if len(err) == 0:
                    setattr(self, c.name, entry)
                else:
                    errors += ["Problem for column '%s': '%s'" % (c.name, e) for e in err]
        return errors, warnings
